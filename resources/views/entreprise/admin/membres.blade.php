@extends('entreprise.layouts.app')

@section('content')

    {{-- Calcul du total d'utilisateurs --}}
    @php
        $totalUsers = count($users);
    @endphp

    <div class="users-container">
        {{-- En-tête de la page --}}
        <div class="users-header">
            <h2 class="users-title">Les Utilisateurs</h2>

            {{-- Bouton d’ajout visible uniquement si l’utilisateur a la permission d’administration --}}
            @if(Auth()->user()->hasPermission("permission.administrateur.gerer.utilisateur"))
                <a href="?create=true" class="btn-add">
                    <i class="fas fa-user-plus"></i>
                    Ajouter un nouvel Utilisateur
                </a>
            @endif
        </div>

        {{-- Tableau listant les utilisateurs --}}
        <table class="users-table">
            <thead>
            <tr>
                <th>Nom & prénom</th>
                <th>Adresse Email</th>
                <th>Numéro TÉL</th>
                <th>Groupe</th>
                <th>Status</th>
                {{-- Affiche la colonne Actions seulement si l'utilisateur n'a PAS la permission admin --}}
                @if(Auth()->user()->hasPermission("permission.administrateur.gerer.utilisateur"))
                    <th>Actions</th>
                @endif
            </tr>
            </thead>

            <tbody>
            {{-- Boucle sur chaque utilisateur --}}
            @foreach($users as $user)
                @php
                    // Masquage de l'email
                    $email = $user->email ?? '';
                    [$localPart, $domainPart] = array_pad(explode('@', $email, 2), 2, '');
                    $maskedSegsEmail = array_map(fn($seg) => str_repeat('*', max(1, strlen($seg))), explode('.', $localPart));
                    $maskedEmail = implode('.', $maskedSegsEmail) . ($domainPart !== '' ? '@' . $domainPart : '');
                    // Masquage du numéro de téléphone (tous les chiffres sauf les 4 derniers)
                    $maskedPhone = preg_replace('/\d(?=\d{4})/', '*', $user->telephone);
                @endphp

                <tr>
                    {{-- Nom complet --}}
                    <td>{{ $user->prenom }} {{ $user->nom }}</td>

                    {{-- Email masqué + version complète dans les attributs data --}}
                    <td class="user-email"
                        data-full-email="{{ $user->email ?? 'Pas d\'email..' }}"
                        data-masked-email="{{ $maskedEmail }}">
                        <span class="masked-email">{{ $maskedEmail }}</span>
                    </td>

                    {{-- Téléphone masqué + version complète dans les attributs data --}}
                    <td class="user-phone"
                        data-full-phone="{{ $user->telephone }}"
                        data-masked-phone="{{ $maskedPhone }}">
                        <span class="masked-phone">{{ $maskedPhone }}</span>
                    </td>

                    {{-- Rôle de l'utilisateur --}}
                    <td>{{ $user->role }}</td>

                    {{-- Statut en ligne ou hors ligne --}}
                    <td>
                        @if($user->isOnlineFromSession(10))
                            <span class="badge badge-online">Online</span>
                        @else
                            <span class="badge badge-offline">Offline</span>
                        @endif
                    </td>

                    {{-- Actions (voir, modifier, supprimer) --}}
                    @if(Auth()->user()->hasPermission("permission.administrateur.gerer.utilisateur"))
                        <td class="actions">
                            <button type="button" class="action-btn view"><i class="fas fa-eye"></i></button>
                            <button type="button" class="action-btn edit"><i class="fas fa-pencil-alt"></i></button>
                            <button type="button" class="action-btn delete"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- Script JS pour afficher / masquer email & téléphone --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Pour chaque bouton "voir"
            document.querySelectorAll('.action-btn.view').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const row = btn.closest('tr'); // ligne concernée

                    // Sélection des cellules
                    const phoneCell = row.querySelector('.user-phone');
                    const tel = phoneCell.querySelector('.masked-phone');
                    const emailCell = row.querySelector('.user-email');
                    const email = emailCell.querySelector('.masked-email');
                    const icon = btn.querySelector('i');

                    // Données depuis les attributs
                    const fullPhone = phoneCell.dataset.fullPhone;
                    const maskedPhone = phoneCell.dataset.maskedPhone;
                    const fullEmail = emailCell.dataset.fullEmail;
                    const maskedEmail = emailCell.dataset.maskedEmail;

                    // Toggle affichage
                    if (icon.classList.contains('fa-eye')) {
                        tel.textContent = fullPhone;
                        email.textContent = fullEmail;
                        icon.classList.replace('fa-eye', 'fa-eye-slash');
                    } else {
                        tel.textContent = maskedPhone;
                        email.textContent = maskedEmail;
                        icon.classList.replace('fa-eye-slash', 'fa-eye');
                    }
                });
            });
        });
    </script>
@endsection
