<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{ProjectController, PageController, PublicationController, TaskController};
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\Auth\RegisteredUserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [PageController::class, 'home']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ----------------------------------------------------------------------
// 1. PRIMA LE ROTTE SPECIFICHE (PI, Manager)
// Devono stare qui in alto, altrimenti "create" viene scambiato per un ID
// ----------------------------------------------------------------------
Route::middleware(['auth', 'role:pi,manager'])->group(function () {
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    // Rotte Pubblicazioni (Protette)
    Route::resource('publications', PublicationController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);

    // Altre rotte protette da ruolo
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    // Rotte per gestire i membri del progetto
    Route::middleware(['auth', 'role:pi,manager'])->group(function () {
        Route::post('/projects/{project}/members', [ProjectController::class, 'addMember'])->name('projects.addMember');
        Route::delete('/projects/{project}/members/{user}', [ProjectController::class, 'removeMember'])->name('projects.removeMember');
    });
    Route::get('/projects/{project}/export', [ProjectController::class, 'exportCsv'])->middleware(['auth', 'role:pi,manager'])->name('projects.export')->name('projects.export');
});

// ----------------------------------------------------------------------
// 2. POI LE ROTTE GENERICHE (Auth standard)
// Qui c'è la resource che contiene 'show' (/projects/{id})
// ----------------------------------------------------------------------
Route::middleware(['auth'])->group(function () {
    // Nota: except store/create/destroy perché sono gestite sopra
    Route::resource('projects', ProjectController::class)->except(['store', 'create', 'destroy']);
    Route::resource('publications', PublicationController::class);
    Route::resource('tasks', TaskController::class);
    Route::get('/projects/json', [ProjectController::class, 'index']);

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/users/edit', [UserController::class, 'edit'])->name('users.edit');
});

// Rotta per CREARE una milestone (collegata al progetto)
Route::post('/projects/{project}/milestones', [MilestoneController::class, 'store'])
    ->middleware(['auth', 'role:pi,manager'])
    ->name('projects.milestones.store');

// Rotta per AGGIORNARE una milestone
Route::put('/milestones/{milestone}', [MilestoneController::class, 'update'])
    ->middleware(['auth', 'role:pi,manager'])
    ->name('milestones.update');

Route::get('/milestones/{milestone}/edit', [App\Http\Controllers\MilestoneController::class, 'edit'])
    ->middleware(['auth', 'role:pi,manager'])
    ->name('milestones.edit');

// Rotta per ELIMINARE una milestone
Route::delete('/milestones/{milestone}', [MilestoneController::class, 'delete'])
    ->middleware(['auth', 'role:pi,manager'])
    ->name('milestones.destroy');

//Rotta middleware per evitare che researcher modifichi progetto
Route::middleware(['auth', 'role:pi,manager'])->group(function () {
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
});

//Route per evitare che ruolo manager modifichi progetto ed elimini progetto di altri



//Rotta per il gruppo di ricerca con middleware
Route::middleware(['auth', 'role:pi,manager'])->group(function () {
    Route::get('/groups', [App\Http\Controllers\GroupController::class, 'edit'])->name('groups.edit');
    Route::post('/groups/update', [App\Http\Controllers\GroupController::class, 'update'])->name('groups.update');
    Route::post('/groups/add-member', [App\Http\Controllers\GroupController::class, 'addMember'])->name('groups.addMember');
    Route::delete('/groups/remove-member/{userId}', [App\Http\Controllers\GroupController::class, 'removeMember'])->name('groups.removeMember');
    Route::post('/groups/sync-members', [App\Http\Controllers\GroupController::class, 'syncMembers'])->name('groups.syncMembers');
});

// Rotta per SALVARE un commento (collegato al progetto)
Route::post('/projects/{project}/comments', [CommentsController::class, 'store'])->name('projects.comments.store');

// Rotta per ELIMINARE un commento
Route::delete('/comments/{comment}', [CommentsController::class, 'destroy'])->name('comments.destroy');

//Rotta per AGGIUNGERE TASKS (collegati al progetto)
Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('projects.tasks.store')->middleware(['auth', 'role:pi,manager']);

//Rotta per AGGIORNARE TASKS
Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update')->middleware(['auth', 'role:pi,manager']);

//Rotta per ELIMINARE TASKS
Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy')->middleware(['auth', 'role:pi,manager']);


require __DIR__.'/auth.php';