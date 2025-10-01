<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Erreur @yield('code')</title>
    <!-- Styles multi-thème embarqués -->
    <style>
        :root {
            --color-bg: #ffffff;
            --color-text: #2d3748;
            --color-primary: #3182ce;
            --color-error: #e53e3e;
            --color-secondary: #edf2f7;
            --color-button-text: #ffffff;
        }
        body.theme-dark {
            --color-bg: #2d3748;
            --color-text: #edf2f7;
            --color-primary: #63b3ed;
            --color-error: #fc8181;
            --color-secondary: #1a202c;
            --color-button-text: #2d3748;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
            background-color: var(--color-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-box {
            background-color: var(--color-bg);
            color: var(--color-text);
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            padding: 2rem;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        .login-logo img {
            width: 80px;
            margin-bottom: 1rem;
        }
        h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--color-error);
        }
        p {
            margin-bottom: 1rem;
        }
        button {
            background-color: var(--color-primary);
            color: var(--color-button-text);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 1rem;
            margin: 0.5rem;
            transition: background-color 0.3s;
        }
        button:hover {
            opacity: 0.9;
        }
        button.success {
            background-color: var(--color-text);
            color: var(--color-bg);
        }
        .message {
            font-size: 0.9rem;
        }
        footer {
            font-size: 0.75rem;
            color: var(--color-text);
            margin-top: 1.5rem;
        }
    </style>
</head>
<body class="{{ $theme ?? 'theme-light' }}">
<div class="login-box">
    <div class="login-logo">
        <img src="{{ asset('images/logo.png') }}" alt="Logo">
    </div>
    <h2>Erreur @yield('code')</h2>
    <p class="message error">@yield('message')</p>

    <div class="message">
        <p>Si le problème persiste, contactez notre support :</p>
        <a href="/support/ticket">Contacter le support</a>
        <p>+33 1 23 45 67 89</p>
    </div>

    <div style="display: flex; flex-direction: column; align-items: center;">
        <button type="button" onclick="window.location='{{ route('dashboard') }}'">Retour au dashboard</button>
        <button type="button" class="success" onclick="window.location='{{ url()->previous() }}'">Page précédente</button>
    </div>

    <footer>&copy; {{ date('Y') }} Obsek.fr. Tous droits réservés.</footer>
</div>
</body>
</html>
