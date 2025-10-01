
@php
    $uuid = $entreprise->uuid;
@endphp
<nav class="sidebar">

    <a href="{{ route('entreprise.dashboard', ['uuid' => $uuid]) }}"
       class="@activeClass('entreprise.dashboard')">
        <i class="fas fa-home"></i> Dashboard
    </a>

    <h4>GESTION DES CÉRÉMONIES</h4>

    {{-- Agenda --}}
    <a href="{{ route('entreprise.agenda.view', ['uuid' => $uuid]) }}"
       class="@activeClass('entreprise.agenda.view')">
        <i class="fas fa-calendar-alt"></i> Agenda
    </a>
    <a href="{{ route('entreprise.agenda.demandes', ['uuid' => $uuid]) }}"
       class="@activeClass('entreprise.agenda.demandes')">
        <i class="fas fa-envelope"></i> Demandes
    </a>

    <h4>GESTION DES PAIEMENTS</h4>

    <a href="{{ route('entreprise.paiement.creation_devis', ['uuid' => $uuid]) }}"
       class="@activeClass('entreprise.paiement.creation_devis')">
        <i class="fas fa-file-invoice"></i> Créer un devis
    </a>
    <a href="{{ route('entreprise.paiement.attentes', ['uuid' => $uuid]) }}"
       class="@activeClass('entreprise.paiement.attentes')">
        <i class="fas fa-hourglass-half"></i> Paiement en attente
    </a>
    <a href="{{ route('entreprise.paiement.effectues', ['uuid' => $uuid]) }}"
       class="@activeClass('entreprise.paiement.effectues')">
        <i class="fas fa-money-check-alt"></i> Paiements effectués
    </a>
    <a href="{{ route('entreprise.paiement.historique', ['uuid' => $uuid]) }}"
       class="@activeClass('entreprise.paiement.historique')">
        <i class="fas fa-history"></i> Historique de paiements
    </a>

    <h4>ADMINISTRATION</h4>

    <a href="{{ route('entreprise.admin.profile', ['uuid' => $uuid]) }}"
       class="@activeClass('entreprise.admin.profile')">
        <i class="fas fa-building"></i> Profil de l’entreprise
    </a>
    <a href="{{ route('entreprise.admin.parametre', ['uuid' => $uuid]) }}"
       class="@activeClass('entreprise.admin.parametre')">
        <i class="fas fa-cog"></i> Paramètres
    </a>
    <a href="{{ route('entreprise.admin.membres', ['uuid' => $uuid]) }}"
       class="@activeClass('entreprise.admin.membres')">
        <i class="fas fa-users"></i> Membres
    </a>
    <a href="{{ route('entreprise.admin.logs', ['uuid' => $uuid]) }}"
       class="@activeClass('entreprise.admin.logs')">
        <i class="fas fa-history"></i> Historique
    </a>

</nav>

