{{--
    app.blade.php — Shell HTML minimal pour la SPA React.

    MISSION : ne contient aucun HTML métier. Sa seule responsabilité est de
    fournir le <div id="app"> dans lequel React se monte (voir frontend/app.jsx)
    et de charger les assets via @vite. Toute la navigation ensuite est gérée
    côté client par React Router (BrowserRouter dans App.jsx) — Laravel ne sert
    plus jamais de HTML pour les routes / , /login, /dashboard, etc.

    RELATION : servi par la route catch-all dans routes/web.php pour toute
    URL qui n'est ni /api/*, ni un fichier statique.
--}}
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'SkanCV') }}</title>
        @fonts
        @vite(['frontend/css/app.css', 'frontend/app.jsx'])
    </head>
    <body class="antialiased">
        <div id="app"></div>
    </body>
</html>
