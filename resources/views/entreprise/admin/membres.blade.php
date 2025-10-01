@extends('entreprise.layouts.app')

@section('content')
    @php
        $totalUsers = count($users);
    @endphp

    <div class="users-container">
        <div class="users-header">
            <h2 class="users-title">Les Utilisateurs</h2>
            @if(Auth()->user()->hasPermission("permission.administrateur.gerer.utilisateur"))
            <a href="?create=true" class="btn-add">
                <i class="fas fa-user-plus"></i>
                Ajouter un nouvel Utilisateur
            </a>
            @endif
        </div>

        <table class="users-table">
            <thead>
            <tr>
                <th>Nom & prénom</th>
                <th>Numéro TÉL</th>
                <th>Groupe</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                @php
                    // masquage email / téléphone (comme précédemment)
                    [$localPart, $domainPart] = explode('@', $user->email) + [1=>''];
                    $maskedSegsEmail = array_map(fn($seg)=>str_repeat('*',max(1,strlen($seg))), explode('.',$localPart));
                    $maskedEmail    = implode('.',$maskedSegsEmail).'@'.$domainPart;
                    $maskedPhone    = preg_replace('/\d(?=\d{4})/','*',$user->telephone);
                @endphp
                <tr>
                    <td>{{ $user->prenom }} {{ $user->nom }}</td>
                    <td>{{ $maskedPhone }}</td>
                    <td>{{ $user->role }}</td>
                    <td>
                        @if($user->isOnlineFromSession(10))
                            <span class="badge badge-online">Online</span>
                        @else
                            <span class="badge badge-offline">Offline</span>
                            <i>{{ optional($user->last_seen)->format('H:i') }}</i>
                        @endif
                    </td>

                    @if(Auth()->user()->hasPermission("permission.administrateur.gerer.utilisateur"))
                        <td class="actions">
                            <button type="button" class="action-btn view"><i class="fas fa-eye"></i></button>
                            <button type="button" class="action-btn edit"><i class="fas fa-pencil-alt"></i></button>
                            <button type="button" class="action-btn delete"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    @else
                        <td class="actions">Aucune action disponible.</td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fonction générique de bascule
            function toggleIcon(btn, span, full, masked) {
                const icon = btn.querySelector('i');
                if (icon.classList.contains('fa-eye')) {
                    span.textContent = full;
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    span.textContent = masked;
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            }

            // Bascule email
            document.querySelectorAll('.btn-toggle-email').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const wrap = btn.closest('.user-email');
                    toggleIcon(
                        btn,
                        wrap.querySelector('.masked-email'),
                        wrap.dataset.fullEmail,
                        wrap.dataset.maskedEmail
                    );
                });
            });

            // Bascule téléphone
            document.querySelectorAll('.btn-toggle-phone').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const wrap = btn.closest('.user-phone');
                    toggleIcon(
                        btn,
                        wrap.querySelector('.masked-phone'),
                        wrap.dataset.fullPhone,
                        wrap.dataset.maskedPhone
                    );
                });
            });
        });
    </script>

@endsection
