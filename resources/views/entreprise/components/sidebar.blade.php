<nav class="sidebar">

    <a href="{{ route('entreprise.dashboard', ['uuid' => $entreprise->uuid]) }}"
       class="@activeClass('entreprise.dashboard')">
        <i class="fas fa-home"></i> Dashboard
    </a>

    <h5>GESTION DES CÉRÉMONIES</h5>

    {{-- Agenda --}}
    <a href="{{ route('entreprise.agenda.calendar', ['uuid' => $entreprise->uuid]) }}"
       class="@activeClass('entreprise.agenda.calendar')">
        <i class="fas fa-calendar-alt"></i> Agenda
    </a>
    <a href="{{ route('entreprise.agenda.demandes', ['uuid' => $entreprise->uuid]) }}"
       class="@activeClass('entreprise.agenda.demandes')">
        <i class="fas fa-envelope"></i> Demandes
    </a>

    <h5>GESTION DES PAIEMENTS</h5>

    <a href="{{ route('entreprise.paiement.creation_devis', ['uuid' => $entreprise->uuid]) }}"
       class="@activeClass('entreprise.paiement.creation_devis')">
        <i class="fas fa-file-invoice"></i> Créer un devis
    </a>
    <a href="{{ route('entreprise.paiement.attentes', ['uuid' => $entreprise->uuid]) }}"
       class="@activeClass('entreprise.paiement.attentes')">
        <i class="fas fa-hourglass-half"></i> Paiement en attente
    </a>
    <a href="{{ route('entreprise.paiement.effectues', ['uuid' => $entreprise->uuid]) }}"
       class="@activeClass('entreprise.paiement.effectues')">
        <i class="fas fa-money-check-alt"></i> Paiements effectués
    </a>
    <a href="{{ route('entreprise.paiement.historique', ['uuid' => $entreprise->uuid]) }}"
       class="@activeClass('entreprise.paiement.historique')">
        <i class="fas fa-history"></i> Historique de paiements
    </a>

    <h5>ADMINISTRATION</h5>

    <a href="{{ route('entreprise.admin.profile', ['uuid' => $entreprise->uuid]) }}"
       class="@activeClass('entreprise.admin.profile')">
        <i class="fas fa-building"></i> Profil de l’entreprise
    </a>
    <a href="{{ route('entreprise.admin.parametre', ['uuid' => $entreprise->uuid]) }}"
       class="@activeClass('entreprise.admin.parametre')">
        <i class="fas fa-cog"></i> Paramètres
    </a>
    <a href="{{ route('entreprise.admin.membres', ['uuid' => $entreprise->uuid]) }}"
       class="@activeClass('entreprise.admin.membres')">
        <i class="fas fa-users"></i> Membres
    </a>
    <a href="{{ route('entreprise.admin.logs', ['uuid' => $entreprise->uuid]) }}"
       class="@activeClass('entreprise.admin.logs')">
        <i class="fas fa-history"></i> Historique
    </a>
</nav>

