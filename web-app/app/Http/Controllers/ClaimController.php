<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\Notification;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClaimController extends Controller
{
    /**
     * Store a new claim.
     */
    public function store(Request $request)
    {
        try {
            if (!auth()->check()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
    
            // Validate the request
            $validated = $request->validate([
                'item_id' => 'required|exists:found_items,id',
                'claim_status' => 'required|in:Pending,Approved,Rejected',
                'submission_date' => 'nullable|date',
                'proof_of_ownership' => 'nullable|file|mimes:jpeg,png,pdf|max:2048', // File validation
            ]);
    
            // If no submission date is provided, set it to the current date
            $submissionDate = $request->submission_date ? Carbon::parse($request->submission_date)->toDateString() : Carbon::now()->toDateString();
    
            // Handle file upload
            $imageUrl = null;
            if ($request->hasFile('proof_of_ownership')) {
                $filename = time() . '.' . $request->proof_of_ownership->extension();
                $imageUrl = $request->file('proof_of_ownership')->storeAs('assets/proof', $filename, 'public');
            }
    
            // Create the claim with the authenticated user's ID
            $claim = Claim::create([
                'item_id' => $validated['item_id'],
                'user_id' => auth()->id(), // Use the authenticated user's ID
                'claim_status' => $validated['claim_status'],
                'submission_date' => $submissionDate, // Use the provided date or current date
                'proof_of_ownership' => $imageUrl,
            ]);
    
            // Find the item (assuming it's from the 'found_items' table)
            $item = FoundItem::findOrFail($validated['item_id']);
    
            // Create notification for the item owner (only if it's not their own claim)
            if ($item->user_id !== auth()->id()) {
                $notificationData = [
                    'title' => 'New Claim',
                    'message' => auth()->user()->name . ' has claimed your found item "' . $item->item_name . '"',
                    'item_id' => $item->id,
                    'item_type' => 'found',
                    'claim_id' => $claim->claim_id,
                    'item_name' => $item->item_name,
                    'claimer_name' => auth()->user()->name,
                    'user_id' => $item->user_id,
                ];
    
                Notification::create([
                    'user_id' => $item->user_id, // Notify the item owner
                    'type' => 'claim',
                    'data' => $notificationData,
                    'read_at' => null
                ]);
            }
    
            // Return a response (redirecting or returning success)
            return redirect('/newsfeed'); 
    
        } catch (\Exception $e) {
            \Log::error('Error creating claim: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to create claim',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    
    
    

    /**
     * Update the claim's status.
     */


    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
    
            $claim = Claim::findOrFail($id);
     
            // Validate the claim status
            $validated = $request->validate([
                'claim_status' => 'required|in:Pending,Approved,Rejected',
            ]);
     
            // Log the validated claim status
            \Log::info("Claim status being updated for claim ID: $id", ['new_status' => $validated['claim_status']]);
     
            // Update the claim status
            $claim->update([
                'claim_status' => $validated['claim_status'],
            ]);
    
            DB::commit();
    
            return response()->json(['success' => true, 'claim' => $claim], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            \Log::error("Claim not found for ID: $id");
            return response()->json(['success' => false, 'message' => 'Claim not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating claim status', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to update claim status'], 500);
        }
    }
    


    /**
     * Update the claim's status.
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            DB::beginTransaction();
    
            // Fetch the claim using claim_id
            $claim = Claim::where('claim_id', $id)->firstOrFail();  // Use claim_id for lookup
    
            // Validate the claim status
            $validated = $request->validate([
                'claim_status' => 'required|in:approved,rejected',
            ]);
    
            // Update the claim status
            $claim->update([
                'claim_status' => ucfirst($validated['claim_status']), // Capitalize first letter
            ]);
    
            DB::commit();
    
            // The claim owner (who made the claim)
            $claimOwner = $claim->user; 
            // The found item that was claimed
            $foundItem = $claim->foundItem; 
    
            // Send notification to claim owner if the status has changed
            // Do not notify the claim owner if the item is not theirs
            if ($claimOwner->id !== Auth::id()) {
                $notificationData = [
                    'title' => 'Claim Status Update',
                    'message' => 'The claim status for your submission "' . $foundItem->item_name . '" has been updated to ' . $claim->claim_status,
                    'item_id' => $foundItem->id,
                    'claim_id' => $claim->claim_id,
                    'item_name' => $foundItem->item_name,
                    'claimer_name' => $claimOwner->name,
                    'status' => $claim->claim_status,
                    'claim_user_id' => $claimOwner->id,  // Added claim_user_id here
                ];
    
                Notification::create([
                    'user_id' => $claimOwner->id,
                    'type' => 'claim',
                    'data' => $notificationData,
                ]);
            }
    
            // Send notification to the item owner (only if the claim status affects their item)
            // Do not notify if the item is owned by the authenticated user (the person making the claim)
            if ($foundItem->user_id !== Auth::id()) {
                $notificationData = [
                    'title' => 'Claim Status Update',
                    'message' => 'The claim status for your found item "' . $foundItem->item_name . '" has been ' . $claim->claim_status,
                    'item_id' => $foundItem->id,
                    'item_type' => 'found',
                    'claim_id' => $claim->claim_id,
                    'item_name' => $foundItem->item_name,
                    'claimer_name' => $claimOwner->name,
                    'status' => $claim->claim_status,
                ];
    
                Notification::create([
                    'user_id' => $foundItem->user_id, // Notify the item owner
                    'type' => 'claim',
                    'data' => $notificationData,
                ]);
            }
    
            \Log::info("Notification sent to claim owner: {$claimOwner->id}", $notificationData);
            \Log::info("Notification sent to item owner: {$foundItem->user_id}", $notificationData);
    
            // Return the updated claim with related data
            return response()->json([
                'success' => true,
                'claim' => $claim->load('user', 'foundItem')
            ], 200);
    
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Claim not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update claim status'
            ], 500);
        }
    }
    
    
    
    
    
    
    /**
     * Show a specific claim.
     */
    public function show($id)
    {
        $claim = Claim::findOrFail($id);
        return response()->json($claim);
    }

    /**
     * Show all claims with related data.
     */

     public function showAll()
     {
        $claims = DB::table('claims')
            ->join('found_items', 'claims.item_id', '=', 'found_items.id')
            ->join('users', 'claims.user_id', '=', 'users.id')
            ->select(
                'claims.claim_id',
                'claims.claim_status',
                'claims.submission_date',
                'claims.proof_of_ownership',
                'found_items.item_name',
                'found_items.description',
                'found_items.image_url',
                'users.name as user_name'
            )
            ->orderBy('claims.submission_date', 'DESC')
            ->get();

        return response()->json(['claims' => $claims]);
     }

    // public function showAll()
    // {
    //     $claims = Claim::with(['user', 'foundItem'])->get()->map(function ($claim) {
    //         $proofUrl = $claim->proof_of_ownership;
    //         if ($proofUrl && !str_starts_with($proofUrl, '/storage/')) {
    //             $proofUrl = '/storage/' . $proofUrl;
    //         }

    //         return [
    //             'claim_id' => $claim->claim_id,
    //             'item_id' => $claim->item_id,
    //             'user_id' => $claim->user_id,
    //             'claim_status' => $claim->claim_status,
    //             'submission_date' => $claim->submission_date,
    //             'proof_of_ownership' => $proofUrl,
    //             'user_name' => $claim->user->name,
    //             'item_name' => $claim->foundItem->item_name,
    //             'description' => $claim->foundItem->description,
    //             'category' => $claim->foundItem->category,
    //             'found_date' => $claim->foundItem->found_date,
    //             'image_url' => $claim->foundItem->image_url,
    //         ];
    //     });

    //     return response()->json($claims);
    // }

   /**
     * Index claims with item details.
     */
    public function index(Request $request)
    {
        $itemId = $request->query('item_id');
        $itemType = $request->query('item_type');

        $item = FoundItem::with('user')->find($itemId);

        return Inertia::render('Claim', [
            'item' => $item,
            'itemType' => $itemType,
        ]);
    }
}