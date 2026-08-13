# 📁 Structure & Code du Projet — SkanCV (Laravel API)

## 🗂️ Structure des Fichiers

```
skancv/
├── .editorconfig
├── .env
├── .env.example
├── .gitattributes
├── .gitignore
├── .npmrc
├── README.md
├── artisan
├── composer.json
├── compose.yaml
├── package.json
├── phpunit.xml
├── vite.config.js
│
├── app/
│   ├── Enums/
│   │   └── AnalysisStatus.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php
│   │   │   ├── Api/
│   │   │   │   ├── AnalysisController.php
│   │   │   │   ├── CvController.php
│   │   │   │   └── JobPostingController.php
│   │   │   └── Auth/
│   │   │       └── AuthController.php
│   │   ├── Requests/
│   │   │   ├── StoreCvRequest.php
│   │   │   ├── StoreJobPostingRequest.php
│   │   │   ├── UpdateJobPostingRequest.php
│   │   │   └── Auth/
│   │   │       ├── LoginRequest.php
│   │   │       └── RegisterRequest.php
│   │   └── Resources/
│   │       ├── AnalysisResource.php
│   │       ├── CvResource.php
│   │       ├── JobPostingResource.php
│   │       └── UserResource.php
│   ├── Jobs/
│   │   └── ProcessCvAnalysis.php
│   ├── Models/
│   │   ├── Analysis.php
│   │   ├── Cv.php
│   │   ├── JobPosting.php
│   │   └── User.php
│   └── Providers/
│       └── AppServiceProvider.php
│
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2026_08_08_194032_create_job_postings_table.php
│   │   ├── 2026_08_08_194131_create_cvs_table.php
│   │   ├── 2026_08_08_194153_create_analyses_table.php
│   │   └── 2026_08_08_224450_create_personal_access_tokens_table.php
│   └── seeders/
│       └── DatabaseSeeder.php
│
├── resources/
│   ├── css/
│   ├── js/
│   │   └── app.js
│   └── views/
│       └── welcome.blade.php
│
└── routes/
    ├── api.php
    ├── console.php
    └── web.php
```

---

## 📄 Fichiers de Code

---

### `composer.json`

```json
{
    "$schema": "https://getcomposer.org/schema.json",
    "name": "laravel/laravel",
    "type": "project",
    "description": "The skeleton application for the Laravel framework.",
    "keywords": [
        "laravel",
        "framework"
    ],
    "license": "MIT",
    "require": {
        "php": "^8.3",
        "laravel/framework": "^13.8",
        "laravel/sanctum": "^4.0",
        "laravel/tinker": "^3.0"
    },
    "require-dev": {
        "fakerphp/faker": "^1.23",
        "laravel/pail": "^1.2.5",
        "laravel/pao": "^1.0.6",
        "laravel/pint": "^1.27",
        "laravel/sail": "^1.65",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.6",
        "phpunit/phpunit": "^12.5.12"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "scripts": {
        "setup": [
            "composer install",
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\"",
            "@php artisan key:generate",
            "@php artisan migrate --force",
            "npm install --ignore-scripts",
            "npm run build"
        ],
        "dev": [
            "Composer\\Config::disableProcessTimeout",
            "npx concurrently -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1 --timeout=0\" \"php artisan pail --timeout=0\" \"npm run dev\" --names=server,queue,logs,vite --kill-others"
        ],
        "test": [
            "@php artisan config:clear --ansi @no_additional_args",
            "@php artisan test"
        ],
        "post-autoload-dump": [
            "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
            "@php artisan package:discover --ansi"
        ],
        "post-update-cmd": [
            "@php artisan vendor:publish --tag=laravel-assets --ansi --force"
        ],
        "post-root-package-install": [
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\""
        ],
        "post-create-project-cmd": [
            "@php artisan key:generate --ansi",
            "@php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\"",
            "@php artisan migrate --graceful --ansi"
        ],
        "pre-package-uninstall": [
            "Illuminate\\Foundation\\ComposerScripts::prePackageUninstall"
        ]
    },
    "extra": {
        "laravel": {
            "dont-discover": []
        }
    },
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true,
        "allow-plugins": {
            "pestphp/pest-plugin": true,
            "php-http/discovery": true
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
```

---

### `vite.config.js`

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
```

---

## 🔷 app/Enums/

---

### `app/Enums/AnalysisStatus.php`

```php
<?php

namespace App\Enums;

/**
 * Cycle de vie d'une analyse de CV.
 */
enum AnalysisStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
```

---

## 🔷 app/Models/

---

### `app/Models/User.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Modèle représentant un utilisateur (HR Staff).
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Attributs assignables en masse
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    // Attributs masqués lors de la sérialisation
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Casts des attributs du modèle
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // Hashe automatiquement le mot de passe
        ];
    }
}
```

---

### `app/Models/JobPosting.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * JobPosting représente une offre d'emploi publiée par un HR Staff.
 *
 * Relation : 1 JobPosting -- * CV
 * (une offre reçoit plusieurs CVs, chaque CV appartient à une seule offre)
 */
class JobPosting extends Model
{
    // HasFactory permet de générer des données de test via des
    // Factories (utile pour les tests unitaires/feature plus tard).
    use HasFactory;

    /**
     * $fillable = liste blanche des champs autorisés à être remplis
     * via l'assignation de masse (JobPosting::create([...])).
     *
     * C'est une protection de sécurité obligatoire : sans ça, Eloquent
     * refuse par défaut le mass assignment (erreur
     * MassAssignmentException), pour éviter qu'un attaquant injecte
     * des champs non prévus (ex: id, timestamps) via une requête HTTP
     * malveillante.
     */
    protected $fillable = [
        'title',
        'description',
        'required_skills',
    ];

    /**
     * $casts définit comment Eloquent doit convertir automatiquement
     * les valeurs entre la base de données (SQL) et PHP.
     *
     * 'required_skills' => 'array' :
     * En base, la colonne est de type JSON (ex: '["PHP","Laravel"]').
     * Sans ce cast, Eloquent te renverrait cette valeur comme une
     * STRING brute JSON. Avec le cast, $jobPosting->required_skills
     * te donne directement un tableau PHP natif (['PHP', 'Laravel']),
     * et à l'inverse, si tu assignes un tableau PHP, Eloquent
     * l'encode automatiquement en JSON avant l'INSERT/UPDATE.
     */
    protected function casts(): array
    {
        return [
            'required_skills' => 'array',
        ];
    }

    /**
     * Relation hasMany : une offre d'emploi a plusieurs CVs soumis.
     *
     * Eloquent déduit automatiquement la clé étrangère à utiliser
     * (job_posting_id) à partir du nom de la méthode/classe courante
     * (JobPosting -> job_posting_id), donc pas besoin de la préciser
     * explicitement ici (mais on pourrait avec
     * hasMany(Cv::class, 'job_posting_id') si on voulait être
     * totalement explicite).
     *
     * Utilisation : $jobPosting->cvs récupère une Collection de
     * tous les CVs liés à cette offre.
     */
    public function cvs(): HasMany
    {
        return $this->hasMany(Cv::class);
    }
}
```

---

### `app/Models/Cv.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Modèle représentant un CV soumis pour une offre d'emploi.
 */
class Cv extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_posting_id',
        'candidate_name',
        'candidate_email',
        'file_path',
        'extracted_text',
        'extracted_skills',
    ];

    protected function casts(): array
    {
        return [
            'extracted_skills' => 'array', // Cast JSON en tableau PHP
        ];
    }

    /**
     * Relation : Un CV appartient à une offre d'emploi.
     */
    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    /**
     * Relation : Un CV a une seule analyse associée.
     */
    public function analysis(): HasOne
    {
        return $this->hasOne(Analysis::class);
    }
}
```

---

### `app/Models/Analysis.php`

```php
<?php

namespace App\Models;

use App\Enums\AnalysisStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant l'analyse et le score de matching d'un CV.
 */
class Analysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'cv_id',
        'status',
        'similarity_score',
        'justification',
        'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'status'           => AnalysisStatus::class, // Cast le statut en Enum
            'similarity_score' => 'float',               // Cast le score en float
            'analyzed_at'      => 'datetime',            // Cast la date d'analyse en datetime
        ];
    }

    /**
     * Relation : Une analyse appartient à un CV.
     */
    public function cv(): BelongsTo
    {
        return $this->belongsTo(Cv::class);
    }
}
```

---

## 🔷 app/Jobs/

---

### `app/Jobs/ProcessCvAnalysis.php`

```php
<?php
// app/Jobs/ProcessCvAnalysis.php

namespace App\Jobs;

use App\Enums\AnalysisStatus;
use App\Models\Analysis;
use App\Models\Cv;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessCvAnalysis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Le CV à traiter.
     * SerializesModels stocke juste l'ID en Redis (pas tout l'objet),
     * puis recharge le modèle frais depuis la DB au moment de l'exécution.
     */
    public function __construct(public Cv $cv)
    {
    }

    /**
     * Combien de fois Laravel réessaie ce job avant de l'envoyer dans failed_jobs.
     * Utile si FastAPI est temporairement indisponible (redémarrage, surcharge).
     */
    public int $tries = 3;

    /**
     * Délai en secondes avant chaque nouvelle tentative (backoff progressif).
     * Évite de re-taper sur FastAPI immédiatement s'il est déjà en difficulté.
     */
    public function backoff(): array
    {
        return [10, 30, 60]; // 10s, puis 30s, puis 60s avant d'abandonner
    }

    /**
     * Garantit qu'une seule analyse tourne à la fois, peu importe quel CV.
     * Clé globale (pas liée à un cv_id précis) car la contrainte métier est
     * "un seul traitement IA en cours dans tout le système", pas juste par CV.
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('cv-analysis-global-lock'))
                ->expireAfter(120), // libère le verrou après 2min max, sécurité anti-blocage définitif
        ];
    }

    /**
     * Le cœur de la recette. Exécuté par le worker, jamais par le controller.
     */
    public function handle(): void
    {
        // On repasse le Cv en PROCESSING pour que le frontend puisse afficher
        // un indicateur "analyse en cours" si le HR Staff rafraîchit la page.
        $this->cv->analysis()->update(['status' => AnalysisStatus::PROCESSING]);

        try {
            // NOTE : l'appel réel à FastApiClient sera branché à l'étape 4.
            // Ici on prépare juste la structure d'accueil du résultat.
            //
            // $extraction = app(FastApiClient::class)->extract($this->cv->file_path);
            // $score      = app(FastApiClient::class)->score($extraction, $this->cv->jobPosting->required_skills);

            Analysis::where('cv_id', $this->cv->id)->update([
                'status' => AnalysisStatus::COMPLETED,
                // 'extracted_skills' => $extraction['skills'],
                // 'score' => $score['score'],
                // 'justification' => $score['justification'],
            ]);

        } catch (Throwable $e) {
            // Toute erreur (réseau, timeout, réponse FastAPI invalide) atterrit ici.
            Log::error('Échec analyse CV', [
                'cv_id' => $this->cv->id,
                'error' => $e->getMessage(),
            ]);

            Analysis::where('cv_id', $this->cv->id)->update([
                'status' => AnalysisStatus::FAILED,
            ]);

            // On relance l'exception pour que Laravel déclenche le retry (tries/backoff)
            throw $e;
        }
    }

    /**
     * Appelé automatiquement par Laravel si TOUTES les tentatives ont échoué
     * (donc le job atterrit dans failed_jobs).
     */
    public function failed(Throwable $exception): void
    {
        Analysis::where('cv_id', $this->cv->id)->update([
            'status' => AnalysisStatus::FAILED,
        ]);

        Log::critical('Analyse CV définitivement échouée', [
            'cv_id' => $this->cv->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
```

---

## 🔷 app/Providers/

---

### `app/Providers/AppServiceProvider.php`

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
```

---

## 🔷 app/Http/Controllers/

---

### `app/Http/Controllers/Controller.php`

```php
<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //
}
```

---

### `app/Http/Controllers/Auth/AuthController.php`

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/register
     * MISSION : créer un compte HR Staff et retourner immédiatement
     * un token d'accès, pour que le frontend puisse enchaîner sans
     * repasser par /login juste après l'inscription.
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name'     => $request->validated('name'),
            'email'    => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

        // createToken() vient de HasApiTokens (Sanctum) sur le modèle User.
        // Le nom "api-token" est arbitraire, sert juste à identifier le
        // token dans la table personal_access_tokens (colonne "name").
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user'  => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * POST /api/login
     * MISSION : vérifier les identifiants et émettre un nouveau token.
     * Sanctum autorise plusieurs tokens actifs par utilisateur en
     * parallèle (multi-appareils) : on ne révoque pas les anciens ici.
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            // ValidationException produit une 422 avec le même format
            // que les erreurs de validation classiques, cohérent avec
            // le reste de l'API (JobPosting/Cv/Analysis en 422 aussi).
            throw ValidationException::withMessages([
                'email' => ['Identifiants incorrects.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user'  => new UserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * POST /api/logout
     * MISSION : révoquer UNIQUEMENT le token utilisé pour cette requête,
     * pas tous les tokens de l'utilisateur (sinon on déconnecterait
     * ses autres sessions/appareils sans qu'il l'ait demandé).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
```

---

### `app/Http/Controllers/Api/JobPostingController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobPostingRequest;
use App\Http\Requests\UpdateJobPostingRequest;
use App\Http\Resources\JobPostingResource;
use App\Models\JobPosting;
use Illuminate\Http\JsonResponse;

class JobPostingController extends Controller
{
    public function index(): JsonResponse
    {
        $jobPostings = JobPosting::all();

        return response()->json(
            JobPostingResource::collection($jobPostings)
        );
    }

    public function store(StoreJobPostingRequest $request): JsonResponse
    {
        $jobPosting = JobPosting::create($request->validated());

        return response()->json(
            new JobPostingResource($jobPosting),
            201
        );
    }

    public function show(JobPosting $jobPosting): JsonResponse
    {
        return response()->json(new JobPostingResource($jobPosting));
    }

    public function update(UpdateJobPostingRequest $request, JobPosting $jobPosting): JsonResponse
    {
        $jobPosting->update($request->validated());

        return response()->json(new JobPostingResource($jobPosting));
    }

    public function destroy(JobPosting $jobPosting): JsonResponse
    {
        $jobPosting->delete();

        return response()->json(null, 204);
    }
}
```

---

### `app/Http/Controllers/Api/CvController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCvRequest;
use App\Http\Resources\CvResource;
use App\Models\Cv;
use App\Models\JobPosting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class CvController extends Controller
{
    /**
     * GET /job-postings/{jobPosting}/cvs
     * MISSION : lister les candidatures reçues pour une offre.
     * with('analysis') évite le N+1 : sans ça, chaque CV déclencherait
     * une requête SQL séparée pour vérifier s'il a une analyse.
     */
    public function index(JobPosting $jobPosting)
    {
        $cvs = $jobPosting->cvs()->with('analysis')->latest()->get();

        return CvResource::collection($cvs);
    }

    /**
     * POST /job-postings/{jobPosting}/cvs
     * MISSION : stocker le PDF de façon privée + créer le CV.
     * extracted_text et extracted_skills restent NULL ici : ils seront
     * remplis par le Job en queue à l'étape suivante (appel FastAPI /extract).
     */
    public function store(StoreCvRequest $request, JobPosting $jobPosting)
    {
        $file = $request->file('file');

        // storage/app/private/cvs/xxxxx.pdf — nom généré par Laravel,
        // jamais de collision, jamais d'URL publique.
        $path = $file->store('cvs', 'local');

        $cv = $jobPosting->cvs()->create([
            'candidate_name'  => $request->validated('candidate_name'),
            'candidate_email' => $request->validated('candidate_email'),
            'file_path'       => $path,
            // extracted_text / extracted_skills : absents ici volontairement,
            // NULL par défaut tant que le pipeline n'est pas passé.
        ]);

        // TODO (étape suivante — queues Redis) :
        // ProcessCvAnalysis::dispatch($cv);
        // Ce Job appellera FastAPI /extract (remplit extracted_text/extracted_skills
        // sur ce Cv), puis /score (crée l'Analysis correspondante).

        return (new CvResource($cv))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * GET /job-postings/{jobPosting}/cvs/{cv}
     */
    public function show(JobPosting $jobPosting, Cv $cv)
    {
        // Vérifie que ce CV appartient bien à cette offre.
        // Sans ce contrôle, n'importe quel cv_id valide serait accessible
        // via n'importe quelle URL d'offre.
        abort_if($cv->job_posting_id !== $jobPosting->id, 404);

        $cv->load('analysis');

        return new CvResource($cv);
    }

    /**
     * DELETE /job-postings/{jobPosting}/cvs/{cv}
     */
    public function destroy(JobPosting $jobPosting, Cv $cv)
    {
        abort_if($cv->job_posting_id !== $jobPosting->id, 404);

        // Fichier supprimé AVANT le record, sinon on perd file_path.
        Storage::disk('local')->delete($cv->file_path);

        $cv->delete(); // cascadeOnDelete() supprime l'Analysis liée en DB

        return response()->noContent(); // 204
    }
}
```

---

### `app/Http/Controllers/Api/AnalysisController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnalysisResource;
use App\Models\Cv;
use App\Models\JobPosting;

class AnalysisController extends Controller
{
    /**
     * GET /job-postings/{jobPosting}/cvs/{cv}/analysis
     * MISSION : consulter le résultat de matching d'un CV.
     * Lecture seule : une Analysis n'est jamais créée via HTTP,
     * uniquement par le pipeline (étape suivante).
     */
    public function show(JobPosting $jobPosting, Cv $cv)
    {
        abort_if($cv->job_posting_id !== $jobPosting->id, 404);

        $analysis = $cv->analysis;

        abort_if($analysis === null, 404, "Analyse pas encore disponible pour ce CV.");

        return new AnalysisResource($analysis);
    }
}
```

---

## 🔷 app/Http/Requests/

---

### `app/Http/Requests/Auth/LoginRequest.php`

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Autorise la connexion de l'utilisateur.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation pour la connexion.
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
```

---

### `app/Http/Requests/Auth/RegisterRequest.php`

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Autorise l'inscription de l'utilisateur.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation pour la création de compte.
     */
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)], // Exige un champ password_confirmation
        ];
    }

    /**
     * Messages d'erreur personnalisés de validation.
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Un compte existe déjà avec cet email.',
        ];
    }
}
```

---

### `app/Http/Requests/StoreJobPostingRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreJobPostingRequest valide les données envoyées lors de la
 * CRÉATION d'une nouvelle offre d'emploi (POST /api/job-postings).
 *
 * Concrètement, quand une requête HTTP arrive sur
 * JobPostingController::store(), Laravel :
 * 1. Instancie cette classe
 * 2. Appelle authorize() → si false, renvoie une erreur 403
 * 3. Appelle rules() → valide les données selon ces règles
 * 4. Si la validation échoue → renvoie automatiquement une erreur
 *    422 avec le détail des champs invalides, SANS jamais exécuter
 *    le code de store()
 * 5. Si tout est valide → store() s'exécute, et $request->validated()
 *    contient les données propres
 */
class StoreJobPostingRequest extends FormRequest
{
    /**
     * authorize() détermine si l'utilisateur a le DROIT de faire
     * cette action (indépendamment de la validité des données).
     *
     * Pour l'instant, on renvoie true partout, car l'authentification
     * JWT n'est pas encore configurée (rappel : décision explicitement
     * en attente de ton encadrant). Une fois JWT en place, on pourra
     * ici vérifier par exemple que l'utilisateur connecté est bien
     * un HR Staff authentifié.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * rules() définit les règles de validation, champ par champ.
     * Retourne un tableau associatif : nom_du_champ => règles.
     */
    public function rules(): array
    {
        return [
            // 'required' : le champ doit être présent et non vide
            // 'string' : doit être une chaîne de caractères
            // 'max:255' : correspond à la limite VARCHAR(255) définie
            // dans la migration (string() par défaut) — on fait
            // exprès de faire correspondre cette règle à la
            // contrainte SQL réelle, pour éviter un cas où la
            // validation PHP accepterait un titre trop long que
            // MySQL rejetterait ensuite avec une erreur SQL brute.
            'title' => 'required|string|max:255',

            // 'string' seul (pas de max) : correspond au type text()
            // en base, qui n'a pas de limite pratique de taille.
            'description' => 'required|string',

            // 'array' : le champ doit être un tableau PHP (envoyé en
            // JSON depuis React comme un tableau ["PHP","Laravel"]).
            'required_skills' => 'required|array',

            // 'required_skills.*' : validation appliquée à CHAQUE
            // élément du tableau required_skills. Ici, chaque
            // compétence doit être une string non vide.
            'required_skills.*' => 'required|string',
        ];
    }
}
```

---

### `app/Http/Requests/UpdateJobPostingRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdateJobPostingRequest valide les données lors de la MODIFICATION
 * d'une offre existante (PUT/PATCH /api/job-postings/{id}).
 *
 * Différence clé avec StoreJobPostingRequest : on utilise 'sometimes'
 * au lieu de 'required'. Ça permet une mise à jour PARTIELLE — le
 * HR Staff peut vouloir modifier uniquement le titre, sans être
 * obligé de renvoyer TOUS les champs à chaque fois.
 */
class UpdateJobPostingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'sometimes' : la règle qui suit (ex: string|max:255)
            // ne s'applique QUE SI le champ est présent dans la
            // requête. Si le champ est absent, aucune erreur —
            // le champ concerné ne sera simplement pas modifié.
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'required_skills' => 'sometimes|required|array',
            'required_skills.*' => 'required|string',
        ];
    }
}
```

---

### `app/Http/Requests/StoreCvRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCvRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pas d'auth candidat prévue dans le scope actuel.
        // Sera revu quand l'auth HR Staff (JWT/Sanctum/Passport) sera tranchée.
        return true;
    }

    /**
     * MISSION : rejeter toute soumission incomplète ou dangereuse AVANT
     * que le controller ne touche au disque ou à la base.
     * On ne valide QUE candidate_name/candidate_email/file : extracted_text
     * et extracted_skills ne sont jamais fournis par le client, ils sont
     * remplis plus tard par le pipeline FastAPI côté serveur.
     */
    public function rules(): array
    {
        return [
            'candidate_name'  => ['required', 'string', 'max:255'],
            'candidate_email' => ['required', 'email', 'max:255'],
            'file' => [
                'required',
                'file',
                'mimes:pdf',   // vérifie le contenu réel, pas juste l'extension
                'max:5120',    // 5 Mo (en Ko)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'candidate_email.email' => 'L\'email fourni n\'est pas valide.',
            'file.mimes'            => 'Le fichier doit être un PDF valide.',
            'file.max'              => 'Le fichier ne doit pas dépasser 5 Mo.',
        ];
    }
}
```

---

## 🔷 app/Http/Resources/

---

### `app/Http/Resources/UserResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Convertit l'utilisateur en tableau (sans les données sensibles).
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'created_at' => $this->created_at,
        ];
    }
}
```

---

### `app/Http/Resources/JobPostingResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * JobPostingResource définit EXPLICITEMENT la structure JSON envoyée
 * à React pour représenter une JobPosting.
 *
 * C'est une liste blanche : seuls les champs écrits ici sortent en
 * JSON, peu importe ce que contient le Model en base. Ça protège
 * contre l'exposition accidentelle de futurs champs sensibles qu'on
 * ajouterait un jour au Model sans y penser.
 */
class JobPostingResource extends JsonResource
{
    /**
     * toArray() transforme l'objet JobPosting ($this, qui représente
     * ici l'instance du Model) en tableau PHP, que Laravel convertit
     * ensuite automatiquement en JSON dans la réponse HTTP.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,

            // Pas besoin de json_decode() manuel ici : grâce au cast
            // 'required_skills' => 'array' défini dans le Model
            // (Étape 2), $this->required_skills est DÉJÀ un tableau
            // PHP natif à ce stade. La Resource en profite directement.
            'required_skills' => $this->required_skills,

            // whenLoaded('cvs') : n'inclut le tableau des CVs QUE SI
            // la relation a été explicitement chargée en amont (via
            // ->load('cvs') ou ->with('cvs') dans le Controller).
            // Sans ça, chaque appel à /api/job-postings déclencherait
            // une requête SQL supplémentaire par offre pour compter
            // ses CVs (problème classique dit "N+1 query"), même
            // quand on n'en a pas besoin (ex: sur la page liste).
            'cvs_count' => $this->whenLoaded('cvs', fn () => $this->cvs->count()),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

---

### `app/Http/Resources/CvResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CvResource extends JsonResource
{
    /**
     * MISSION : ne JAMAIS exposer file_path (chemin serveur interne)
     * ni extracted_text en entier (potentiellement volumineux, brut).
     * extracted_skills est exposé car c'est un résumé structuré utile
     * au frontend pour un affichage rapide, une fois le pipeline passé.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'job_posting_id'    => $this->job_posting_id,
            'candidate_name'    => $this->candidate_name,
            'candidate_email'   => $this->candidate_email,
            'extracted_skills'  => $this->extracted_skills, // null tant que /extract n'est pas passé
            'analysis_status'   => $this->whenLoaded('analysis', fn () =>
                $this->analysis?->status?->value
            ),
            'created_at'        => $this->created_at->toIso8601String(),
        ];
    }
}
```

---

### `app/Http/Resources/AnalysisResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnalysisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'cv_id'             => $this->cv_id,
            'status'            => $this->status->value,
            'similarity_score'  => $this->similarity_score,
            'justification'     => $this->justification,
            'analyzed_at'       => $this->analyzed_at?->toIso8601String(),
        ];
    }
}
```

---

## 🔷 routes/

---

### `routes/api.php`

```php
<?php

use App\Http\Controllers\Api\JobPostingController;
use App\Http\Controllers\Api\CvController;
use App\Http\Controllers\Api\AnalysisController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/**
 * Toutes les routes définies ici sont automatiquement préfixées par
 * "/api" (configuré dans bootstrap/app.php).
 */

// Routes d'authentification publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées par authentification (Sanctum)
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // Profil de l'utilisateur connecté
    Route::get('/me', function (Illuminate\Http\Request $request) {
        return new App\Http\Resources\UserResource($request->user());
    });

    // Gestion des offres d'emploi
    Route::apiResource('job-postings', JobPostingController::class);

    // Gestion des CVs liés à une offre d'emploi
    Route::apiResource('job-postings.cvs', CvController::class)
        ->except(['update']);

    // Consultation de l'analyse d'un CV (ressource singleton, pas d'apiResource)
    Route::get('job-postings/{jobPosting}/cvs/{cv}/analysis', [AnalysisController::class, 'show'])
        ->name('job-postings.cvs.analysis.show');
});
```

---

### `routes/web.php`

```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
```

---

## 🔷 database/migrations/

---

### `database/migrations/2026_08_08_194032_create_job_postings_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * up() : ce qui se passe quand on lance `sail artisan migrate`.
     * On décrit ici la structure de la table job_postings.
     */
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            // id() = clé primaire auto-incrémentée (BIGINT UNSIGNED).
            // C'est cette valeur qui sera référencée en foreign key
            // par la table `cvs` plus tard (job_posting_id).
            $table->id();

            // Titre de l'offre, ex: "Développeur Laravel Senior".
            // string() = VARCHAR(255) par défaut, largement suffisant.
            $table->string('title');

            // Description complète de l'offre (missions, contexte...).
            // text() = pas de limite de taille pratique (contrairement
            // à string), adapté à du contenu long.
            $table->text('description');

            // Compétences requises pour le poste.
            // On stocke en JSON pour avoir une liste structurée
            // (ex: ["Laravel", "PHP", "MySQL"]) plutôt qu'une simple
            // string non structurée. Cette liste sera comparée aux
            // compétences extraites du CV pour calculer le score.
            $table->json('required_skills');

            // created_at + updated_at (gérés automatiquement par
            // Eloquent). Utile pour savoir depuis quand l'offre est
            // publiée et si elle a été modifiée.
            $table->timestamps();
        });
    }

    /**
     * down() : l'opération inverse, appelée par
     * `sail artisan migrate:rollback`. Elle doit annuler
     * exactement ce que up() a fait.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
```

---

### `database/migrations/2026_08_08_194131_create_cvs_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cvs', function (Blueprint $table) {
            $table->id();

            // === CLÉ ÉTRANGÈRE VERS job_postings ===
            // foreignId() crée une colonne BIGINT UNSIGNED nommée
            // job_posting_id.
            // constrained() ajoute automatiquement la contrainte de
            // clé étrangère vers la table `job_postings` (colonne id),
            // Laravel devine le nom de la table à partir du nom de
            // colonne (job_posting_id -> job_postings).
            // cascadeOnDelete() = si l'offre d'emploi est supprimée,
            // tous les CVs associés sont supprimés automatiquement
            // (cohérence référentielle : un CV n'a pas de sens sans
            // son offre).
            $table->foreignId('job_posting_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Informations du candidat qui a soumis le CV.
            $table->string('candidate_name');
            $table->string('candidate_email');

            // Chemin de stockage du fichier PDF sur le disque Laravel.
            // Rappel architecture : storage/app/private/cvs/...
            // On ne stocke JAMAIS une URL publique ici, uniquement
            // le chemin interne (le fichier n'est jamais exposé
            // directement, on doit passer par un contrôleur qui
            // vérifie les droits d'accès).
            $table->string('file_path');

            // Texte brut extrait du PDF par le endpoint FastAPI /extract.
            // nullable() car au moment de l'upload, l'extraction n'a
            // pas encore eu lieu (elle se fait de façon asynchrone
            // via la queue Redis).
            $table->longText('extracted_text')->nullable();

            // Compétences extraites du texte du CV par le microservice
            // (ex: ["PHP", "Laravel", "Git"]). Stockées en JSON pour
            // pouvoir être comparées facilement aux required_skills
            // de la job_posting. nullable() pour la même raison que
            // extracted_text : rempli après traitement asynchrone.
            $table->json('extracted_skills')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cvs');
    }
};
```

---

### `database/migrations/2026_08_08_194153_create_analyses_table.php`

```php
<?php

use App\Enums\AnalysisStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analyses', function (Blueprint $table) {
            $table->id();

            // Clé étrangère vers la table cvs
            $table->foreignId('cv_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Index unique pour assurer la relation 1-to-1
            $table->unique('cv_id');

            $table->string('status')
                  ->default(AnalysisStatus::PENDING->value);

            $table->decimal('similarity_score', 5, 4)->nullable();

            $table->text('justification')->nullable();

            $table->timestamp('analyzed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analyses');
    }
};
```

---

## 🔷 database/seeders/

---

### `database/seeders/DatabaseSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
```

---

> 📅 Généré le 2026-08-12 — Projet SkanCV (Laravel API)
