<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| SkanCV est une SPA React découplée de Laravel (voir routes/api.php pour
| l'API JWT réelle). Cette route catch-all sert UNIQUEMENT le shell HTML
| (resources/views/app.blade.php) pour toute URL qui n'est pas déjà gérée
| par routes/api.php. React Router prend ensuite le relais côté client
| pour /login, /, et toutes les routes futures — Laravel ne fait plus de
| routing de page, juste de l'API + le service du shell initial.
|
| Le pattern 'where' exclut explicitement 'api/*' pour éviter tout
| conflit avec les routes API déjà enregistrées.
|
*/

Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api).*$');
