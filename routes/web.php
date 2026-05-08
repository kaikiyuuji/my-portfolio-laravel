<?php

use App\Http\Controllers\Admin\ExperienceController as AdminExperienceController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\SocialLinkController as AdminSocialLinkController;
use App\Http\Controllers\Admin\StackController as AdminStackController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// ─── Admin Routes (protected by auth middleware) ──────────────
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('admin.dashboard');

    // Portfolio Profile (Profile model — NOT User model)
    Route::get('/profile', [AdminProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::put('/profile', [AdminProfileController::class, 'update'])->name('admin.profile.update');

    // Stacks (technologies). Reorder must be defined BEFORE the resource
    // so /admin/stacks/reorder doesn't get captured by the {stack} param.
    Route::put('/stacks/reorder', [AdminStackController::class, 'reorder'])->name('admin.stacks.reorder');
    Route::resource('stacks', AdminStackController::class)
        ->except(['show'])
        ->names('admin.stacks');

    // Experiences (CRUD only — no reorder, ordering is automatic by start_date DESC).
    Route::resource('experiences', AdminExperienceController::class)
        ->except(['show'])
        ->names('admin.experiences');

    // Projects. Reorder must precede the resource (same reasoning as Stacks).
    Route::put('/projects/reorder', [AdminProjectController::class, 'reorder'])->name('admin.projects.reorder');
    Route::resource('projects', AdminProjectController::class)
        ->except(['show'])
        ->names('admin.projects');

    // Social Links. Reorder must precede the resource.
    Route::put('/social-links/reorder', [AdminSocialLinkController::class, 'reorder'])->name('admin.social-links.reorder');
    Route::resource('social-links', AdminSocialLinkController::class)
        ->except(['show'])
        ->names('admin.social-links');
});

// ─── Breeze User Account Routes ───────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
