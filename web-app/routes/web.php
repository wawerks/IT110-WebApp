<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LostItemController; 
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\FoundItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\MarkerController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\CommentController;
use App\Models\ClaimStatusReport;
use App\Http\Middleware\CheckRole;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/claim-status-reports', function () {
    return ClaimStatusReport::all();
});

Route::resource('users', UserController::class);
Route::put('/users/{id}/edit', [UserController::class, 'update']);
Route::get('/user-id', [UserController::class, 'getID'])->name('user.id');
// API route to log user activity
Route::post('/log-activity', [ActivityLogController::class, 'logUserActivity']);

// Claims Routes
Route::get('/claims', [ClaimController::class, 'index']);
Route::post('/claims', [ClaimController::class, 'store']);
Route::put('/claims/{id}', [ClaimController::class, 'update']);
Route::get('/claims/{id}', [ClaimController::class, 'show']);
Route::get('/claim-items', [ClaimController::class, 'showAll']);
Route::patch('/claims/{id}/status', [ClaimController::class, 'updateStatus']);

// Notification Routes (Authenticated)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::put('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('notifications', [NotificationController::class, 'store']);
});

// Home Route
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Dashboard Route
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Add the newsfeed route here
    Route::get('/newsfeed', function () {
        return Inertia::render('NewsFeed'); // 'NewsFeed' is the Vue component name
    })->middleware('auth'); // Add auth middleware if needed
});

// Admin Routes
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/users-log', [AdminController::class, 'usersLog'])->name('admin.users-log');
    Route::get('/reported-items', [AdminController::class, 'reportedItems'])->name('admin.reported-items');
});

Route::middleware([CheckRole::class . ':admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/admin/users-log', [AdminController::class, 'usersLog'])->name('admin.usersLog');
    Route::get('/admin/reported-items', [AdminController::class, 'reportedItems'])->name('admin.reportedItems');
    Route::post('/admin/users', [AdminController::class, 'store']);
    Route::put('/admin/users/{id}', [AdminController::class, 'updateUser']);
    Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser']);

    // Claims routes
    Route::patch('/admin/claims/{claim}/status', [ClaimController::class, 'updateStatus'])->name('admin.claims.update-status');
});

// Lost Items routes
Route::prefix('lost-items')->group(function () {
    Route::get('/', [lostItemController::class, 'index'])->name('lost-items.index'); // List all lost items
    Route::get('{id}', [lostItemController::class, 'show'])->name('lost-items.show'); // Display a specific lost item
    Route::post('/', [lostItemController::class, 'store'])->name('lost-items.store'); // Store a new lost item
    Route::put('{id}', [lostItemController::class, 'update'])->name('lost-items.update'); // Update an existing lost item
    Route::delete('{id}', [lostItemController::class, 'destroy'])->name('lost-items.destroy'); // Delete a lost item
    Route::get('/user/{userId}', [LostItemController::class, 'getLostItemsByUser']);
});

Route::prefix('comments')->group(function () {
    Route::get('/{item_type}/{item_id}', [CommentController::class, 'index'])->name('comments.index');
    Route::get('/', [CommentController::class, 'view'])->name('comments.view');
    Route::post('/', [CommentController::class, 'store'])->name('comments.store');
});

// Found Items Routes
// Route::resource('found-items', FoundItemController::class);
Route::prefix('found-items')->group(function () {
    Route::get('/', [FoundItemController::class, 'index'])->name('found-items.index'); // List all found items
    Route::get('{id}', [FoundItemController::class, 'show'])->name('found-items.show'); // Display a specific found item
    Route::post('/', [FoundItemController::class, 'store'])->name('found-items.store'); // Store a new found item
    Route::put('{id}', [FoundItemController::class, 'update'])->name('found-items.update'); // Update an existing found item
    Route::delete('{id}', [FoundItemController::class, 'destroy'])->name('found-items.destroy'); // Delete a found item
});

Route::get('/session', [SessionController::class, 'show']); // To view session details
Route::delete('/session', [SessionController::class, 'destroy']); // To destroy session

// Comment Routes
Route::post('/api/comments', [CommentController::class, 'store']);
Route::get('/api/comments/{id}', [CommentController::class, 'show']);

require __DIR__.'/auth.php';


// Comment Routes
Route::post('/api/comments', [CommentController::class, 'store']);
Route::get('/api/comments/{id}', [CommentController::class, 'show']);

require __DIR__.'/auth.php';


// Comment Routes
Route::post('/api/comments', [CommentController::class, 'store']);
Route::get('/api/comments/{id}', [CommentController::class, 'show']);

require __DIR__.'/auth.php';


// Comment Routes
Route::post('/api/comments', [CommentController::class, 'store']);
Route::get('/api/comments/{id}', [CommentController::class, 'show']);

require __DIR__.'/auth.php';
