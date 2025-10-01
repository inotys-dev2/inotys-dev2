<x-guest-layout>
    {{-- Logo en haut de page --}}
    <div class="login-logo">
        <img src="{{ asset('/images/memorys_logo.png') }}" alt="logo de votre entreprise">
    </div>

    <div class="login-form">
        {{-- Titre de la page --}}
        <h2>Connexion</h2>

        {{-- Affichage des messages d'erreurs de validation, si présents --}}
        @if ($errors->any())
            <div class="message error">
                <ul>
                    @foreach ($errors->all() as $error)
                        {{-- Chaque erreur s'affiche dans un <li> --}}
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Formulaire de connexion vers la route nommée "login" (POST /login) --}}
        <form method="POST" action="{{ route('login') }}">
            {{-- Jeton CSRF obligatoire pour sécuriser la requête POST --}}
            @csrf

            {{-- Champ identifiant (email ou username selon votre backend) --}}
            <div>
                <input
                    type="text"                 {{-- type texte pour accepter email/username au choix --}}
                name="email"
                    id="email"
                    value="{{ old('email') }}"  {{-- conserve la valeur en cas d'erreur de validation --}}
                    autocomplete="username"     {{-- "username" est standard pour le champ d'identifiant --}}
                    required
                    inputmode="email"           {{-- clavier optimisé sur mobile, sans forcer la validation email --}}
                >
                <label for="email">Votre identifiant</label>
            </div>

            {{-- Champ mot de passe --}}
            <div>
                <input
                    type="password"
                    name="password"
                    id="password"
                    autocomplete="current-password"  {{-- aide le navigateur à proposer l'auto-complétion --}}
                    required
                >
                <label for="password">Votre mot de passe</label>
            </div>

            {{-- Lien de réinitialisation du mot de passe si la route existe --}}
            @if (Route::has('password.request'))
                <div class="forgot-password">
                    <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
                </div>
            @endif

            {{-- Bouton de soumission du formulaire --}}
            <button type="submit">
                Se connecter
            </button>
        </form>
    </div>
</x-guest-layout>
