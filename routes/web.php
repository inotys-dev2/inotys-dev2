<?php

use App\Http\Controllers\Admin\AdminSupportController;
use App\Http\Controllers\Auth\NotificationController;
use App\Http\Controllers\Entreprise\EntrepriseAdminController;
use App\Http\Controllers\Entreprise\EntrepriseAgendaController;
use App\Http\Controllers\Entreprise\EntrepriseController;
use App\Http\Controllers\Entreprise\EntreprisePaiementController;
use App\Http\Controllers\Paroisses\ParoissesAgendaController;
use App\Http\Controllers\Paroisses\ParoissesController;
use App\Http\Controllers\Paroisses\ParoissesDemandesController;
use App\Http\Controllers\Paroisses\ParoissesPaiementController;
use App\Http\Controllers\Paroisses\ParoissesParametreController;
use App\Http\Controllers\Paroisses\ParoissesProfileController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CalendarController;


/**
 * Route de démarrage.
 * */
Route::get('/', function () {
    return view('welcome');
});


/**
 * Route dashboard qui permet de rediriger l'utilisateur via son access* definie.
 * puis dans la bonne entreprise ou paroisse via l'uuid definie par l'entreprise lors de sa création.
 *
 * Access: récuperation de l'access via la base de donnée (entreprise, paroisse)
 *
 * En cas d'erreur il reviendra forcement sur /dashboard.
 */
Route::middleware(['auth'])->get('/dashboard', function () {
    $user = auth()->user();
    return match(strtolower($user->access)) {

        'admin' => redirect()->route('admin.dashboard'),
        'entreprise' => redirect()->route('entreprise.dashboard', [
            'uuid' => $user->entreprises->first()?->uuid,
        ]),
        'paroisse' => redirect()->route('paroisses.dashboard', [
            'uuid' => $user->paroisses->first()?->uuid,
        ]),
        default => abort(403),
    };
})->name('dashboard');


/** Route debug (Logout)
 *
 * Permet au développeur de tester rapidement une déconnexion.
 * Redirection /
 */
Route::get('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
});


/**
 * Route utilisateur non connecter.
 *
 * Access au support pour la correction du bug potentiel et à la création de ticket.
 */
Route::middleware('guest')->group(function () {
    Route::get('/support', [AdminSupportController::class, 'index'])->name('support');
    Route::get('/support/create-ticket', [AdminSupportController::class, 'createTicket'])->name('support.create-ticket');
});

/**
 * Route exclusivement pour les administrateur connecter.
 *
 * Permet d'aller sur le dashboard.
 */
Route::middleware(['auth', 'access:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});


/**
 * Route exclusivement pour les utilisateurs d'entreprises obligatoirement connectées
 */
Route::middleware(['auth', 'verified', 'access:entreprise'])
    ->prefix('entreprise/{uuid}')
    ->whereUuid('uuid')
    ->as('entreprise.')
    ->group(function () {

        // Dash + pages
        Route::get('/dashboard', [EntrepriseController::class, 'dashboard'])->name('dashboard');

        // Demandes
        Route::prefix('demandes')->as('agenda.demandes.')->group(function () {
            Route::get('/', [EntrepriseAgendaController::class, 'showAllDemande'])->name('index');
            Route::get('/{id}', [EntrepriseAgendaController::class, 'detailDemande'])
                ->whereNumber('id')->name('detail');
        });

        // Paiements
        Route::prefix('paiement')->as('paiement.')->group(function () {
            Route::get('/creation_devis', [EntreprisePaiementController::class, 'creationDevis'])->name('creation_devis');
            Route::get('/attentes', [EntreprisePaiementController::class, 'attentes'])->name('attentes');
            Route::get('/effectues', [EntreprisePaiementController::class, 'effectues'])->name('effectues');
            Route::get('/historique', [EntreprisePaiementController::class, 'historique'])->name('historique');
        });

        // Admin
        Route::prefix('admin')->as('admin.')->group(function () {
            Route::get('/profile',   [EntrepriseAdminController::class, 'profile'])->name('profile');
            Route::get('/parametre', [EntrepriseAdminController::class, 'parameters'])->name('parametre');
            Route::get('/membres',   [EntrepriseAdminController::class, 'membres'])->name('membres');
            Route::get('/logs',      [EntrepriseAdminController::class, 'logs'])->name('logs');
        });

        // Calendar
        Route::prefix('calendar')->as('agenda.calendar.')->group(function () {
            Route::get('/',        [CalendarController::class, 'indexEntreprise'])->name('index');
            Route::get('/events',  [CalendarController::class, 'events'])->name('events');
            Route::post('/events', [CalendarController::class, 'store'])
                ->middleware('throttle:ceremony') // limite la création
                ->name('store');
        });
    });

/**
 * Route exclusivement pour les utilisateurs de paroisse obligatoirement connectées
 */
Route::middleware(['auth', 'access:paroisse'])->group(function () {
    Route::get('paroisses/{uuid}/dashboard',                     [ParoissesController::class, 'dashboard'])        ->name('paroisses.dashboard');
    Route::get('paroisses/{uuid}/demandes',                      [ParoissesDemandesController::class, 'index'])    ->name('paroisses.demandes');
    Route::get('paroisses/{uuid}/paiement',                      [ParoissesPaiementController::class, 'index'])    ->name('paroisses.paiement');
    Route::get('paroisses/{uuid}/admin/parametre',               [ParoissesParametreController::class, 'index'])   ->name('paroisses.parametre');
    Route::post('paroisses/{uuid}/admin/parametre',              [ParoissesParametreController::class, 'update'])  ->name('paroisses.parametre.update');
    Route::get('paroisses/{uuid}/admin/profile',                 [ParoissesProfileController::class, 'show'])      ->name('paroisses.profile');
    Route::post('paroisses/{uuid}/admin/profile',                [ParoissesProfileController::class, 'update'])    ->name('paroisses.profile.update');

    Route::prefix('paroisses/{uuid}/calendar')->group(function () {
        Route::get('/', [CalendarController::class, 'indexParoisse'])->name('paroisses.calendar');
        Route::get('/events', [CalendarController::class, 'events'])->name('paroisses.calendar.events');
        Route::patch('/events/{ceremony}', [CalendarController::class, 'update'])->whereNumber('ceremony')->name('paroisses.calendar.update');
        Route::get('/availability', [CalendarController::class, 'availability'])->name('paroisses.availability');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::get('/profile',                      [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',                    [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',                   [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/notifications',        [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/profile/notifications',       [NotificationController::class, 'send-read'])->name('notifications.reading');
});


//recuperation du fichier auth.php
require __DIR__.'/auth.php';
