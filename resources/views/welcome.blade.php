<x-guest-layout>
    <div class="login-logo">
        <img src="{{ asset('/images/memorys_logo.png') }}" class="" alt="logo de votre entreprise">
    </div>

    <div class="login-form">
        <h2>Connexion</h2>

        {{-- Affichage des erreurs de validation --}}
        @if ($errors->any())
            <div class="message error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <input
                    type="text"
                    name="email"
                    id="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    required
                >
                <label for="email">Votre identifiant</label>
            </div>

            <div>
                <input
                    type="password"
                    name="password"
                    id="password"
                    autocomplete="current-password"
                    required
                >
                <label for="password">Votre mot de passe</label>
            </div>

            <button type="submit">
                Se connecter
            </button>
        </form>
    </div>
</x-guest-layout>
