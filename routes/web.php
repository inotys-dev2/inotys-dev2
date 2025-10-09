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
Route::middleware(['auth', 'access:entreprise'])->group(function () {
     Route::get('entreprise/{uuid}/dashboard',               [EntrepriseController::class, 'dashboard'])->name('entreprise.dashboard');
     Route::get('entreprise/{uuid}/agenda/view',             [EntrepriseAgendaController::class, 'agenda'])->name('entreprise.agenda.view');
     Route::post('entreprise/{uuid}/agenda/working-days',    [EntrepriseAgendaController::class, 'getWorkingDays'])->name('entreprise.agenda.working-days');
     Route::get('entreprise/{uuid}/agenda/demande',          [EntrepriseAgendaController::class, 'showForm'])->name('entreprise.agenda.demande');
     Route::post('entreprise/{uuid}/agenda/demande',         [EntrepriseAgendaController::class, 'envoyer'])->name('entreprise.agenda.envoyer');
     Route::get('entreprise/{uuid}/agenda/demandes',         [EntrepriseAgendaController::class, 'showAllDemande'])->name('entreprise.agenda.demandes');
     Route::get('entreprise/{uuid}/agenda/demandes/{id}',    [EntrepriseAgendaController::class, 'detailDemande'])->name('entreprise.agenda.demandes.detail');
     Route::get('entreprise/{uuid}/paiement/creation_devis', [EntreprisePaiementController::class, 'creationDevis'])->name('entreprise.paiement.creation_devis');
     Route::get('entreprise/{uuid}/paiement/attentes',       [EntreprisePaiementController::class, 'attentes'])->name('entreprise.paiement.attentes');
     Route::get('entreprise/{uuid}/paiement/effectues',      [EntreprisePaiementController::class, 'effectues'])->name('entreprise.paiement.effectues');
     Route::get('entreprise/{uuid}/paiement/historique',     [EntreprisePaiementController::class, 'historique'])->name('entreprise.paiement.historique');
     Route::get('entreprise/{uuid}/admin/profile',           [EntrepriseAdminController::class, 'profile'])->name('entreprise.admin.profile');
     Route::get('entreprise/{uuid}/admin/parametre',         [EntrepriseAdminController::class, 'parameters'])->name('entreprise.admin.parametre');
     Route::get('entreprise/{uuid}/admin/membres',           [EntrepriseAdminController::class, 'membres'])->name('entreprise.admin.membres');
     Route::get('entreprise/{uuid}/admin/logs',              [EntrepriseAdminController::class, 'logs'])->name('entreprise.admin.logs');

    Route::prefix('entreprise/{uuid}/agenda/calendar')->group(function () {
        Route::get('/', [CalendarController::class, 'index'])->defaults('scope', 'entreprise')->name('entreprise.calendar');
        Route::get('/events',   [CalendarController::class, 'events'])->defaults('scope', 'entreprise')->name('entreprise.calendar.events');
        Route::post('/events',  [CalendarController::class, 'store'])->defaults('scope', 'entreprise')->name('entreprise.calendar.store');
        Route::patch('/events/{ceremony}',  [CalendarController::class, 'update'])->whereNumber('ceremony')->defaults('scope', 'entreprise')->name('entreprise.calendar.update');
        Route::delete('/events/{ceremony}', [CalendarController::class, 'destroy'])->whereNumber('ceremony')->defaults('scope', 'entreprise')->name('entreprise.calendar.destroy');
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
        Route::get('/', [CalendarController::class, 'index'])->defaults('scope', 'paroisse')->name('paroisses.calendar');
        Route::get('/events', [CalendarController::class, 'events'])->defaults('scope', 'paroisse')->name('paroisses.calendar.events');
        Route::post('/events', [CalendarController::class, 'store'])->defaults('scope', 'paroisse')->name('paroisses.calendar.store');
        Route::patch('/events/{ceremony}', [CalendarController::class, 'update'])->whereNumber('ceremony')->defaults('scope', 'paroisse')->name('paroisses.calendar.update');
        Route::delete('/events/{ceremony}', [CalendarController::class, 'destroy'])->whereNumber('ceremony')->defaults('scope', 'paroisse')->name('paroisses.calendar.destroy');
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
