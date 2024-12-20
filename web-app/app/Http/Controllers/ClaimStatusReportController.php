<?php

namespace App\Http\Controllers;

use App\Models\ClaimStatusReport;

class ClaimStatusReportController extends Controller
{
    public function index()
    {
        // Fetch the data from the view (Claim_Status_Report)
        $items = ClaimStatusReport::all();
        
        // Return as JSON
        return response()->json($items);
    }
}