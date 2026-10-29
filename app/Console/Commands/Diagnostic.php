<?php

namespace App\Console\Commands;

use App\Http\Middleware\EnsureAdmin;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Throwable;

class Diagnostic extends Command
{
    protected $signature = 'mayele:diagnostic';

    protected $description = 'Vérifie que l\'installation de Mayele est complète et sécurisée.';

    private int $echecs = 0;

    private int $alertes = 0;

    public function handle(): int
    {
        $this->line('<options=bold>Diagnostic Mayele</>');

        $this->section('Base de données');
        $connectee = $this->verifierConnexion();

        if ($connectee) {
            $this->verifierMigrations();
            $this->verifierTables();
        }

        $this->section('Sécurité');
        $this->verifierRoutesAdmin();
        $this->verifierVisiteur();

        if ($connectee) {
            if (User::where('role', 'admin')->exists()) {
                $this->ok('Un compte administrateur existe.');
            } else {
                $this->echec('Aucun compte administrateur.', 'Renseigne ADMIN_EMAIL et ADMIN_PASSWORD dans .env puis : php artisan db:seed --class=AdminSeeder');
            }
        }

        if (app()->isProduction() && config('app.debug')) {
            $this->echec('APP_DEBUG est activé en production.', 'Mets APP_DEBUG=false dans .env.');
        }

        $this->section('Environnement');
        $this->verifierEnvironnement();

        $this->newLine();

        if ($this->echecs === 0 && $this->alertes === 0) {
            $this->line('<fg=green;options=bold>Tout est en ordre.</>');
        } else {
            $this->line("<options=bold>Résultat : {$this->echecs} problème(s) à corriger, {$this->alertes} avertissement(s).</>");
        }

        return $this->echecs === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function verifierConnexion(): bool
    {
        try {
            DB::connection()->getPdo();
            $this->ok('Connexion à la base « '.DB::connection()->getDatabaseName().' ».');

            return true;
        } catch (Throwable $e) {
            $this->echec('Connexion à la base impossible : '.$e->getMessage(), 'Vérifie DB_* dans .env et que MySQL est démarré dans Laragon.');

            return false;
        }
    }

    private function verifierMigrations(): void
    {
        try {
            $migrator = app('migrator');
            $fichiers = $migrator->getMigrationFiles(array_merge([database_path('migrations')], $migrator->paths()));
            $repo = $migrator->getRepository();
            $executees = $repo->repositoryExists() ? $repo->getRan() : [];
            $attente = array_values(array_diff(array_keys($fichiers), $executees));

            if ($attente === []) {
                $this->ok('Toutes les migrations sont exécutées.');
            } else {
                $this->echec(count($attente).' migration(s) non exécutée(s) : '.implode(', ', array_slice($attente, 0, 4)).(count($attente) > 4 ? '…' : ''), 'Lance : php artisan migrate');
            }
        } catch (Throwable $e) {
            $this->alerte('Impossible de lire l\'état des migrations : '.$e->getMessage());
        }
    }

    private function verifierTables(): void
    {
        $groupes = [
            'Socle' => [
                'sentinelle' => null,
                'tables' => [
                    'users' => ['telephone', 'universite_id', 'filiere_id', 'niveau', 'role', 'code_parrainage', 'parrain_id'],
                    'sessions' => [], 'cache' => [],
                    'filieres' => ['theme'], 'universites' => ['type'], 'plans' => ['duree_jours', 'prix'],
                    'matieres' => ['niveau', 'semestre', 'publie'], 'chapitres' => ['matiere_id', 'est_gratuit'],
                    'annales' => ['statut'], 'abonnements' => ['fin_le'], 'paiements' => ['reference'], 'progressions' => ['termine_le'],
                ],
            ],
            'Quiz, classement, parrainage' => [
                'sentinelle' => 'Models/Quiz.php',
                'tables' => [
                    'recompenses' => ['type', 'source_id'], 'quizzes' => [], 'quiz_questions' => [], 'quiz_choix' => [], 'quiz_tentatives' => ['score'],
                ],
            ],
            'Coach IA' => [
                'sentinelle' => 'Models/CoachConversation.php',
                'tables' => ['coach_conversations' => ['mode'], 'coach_messages' => ['role', 'tokens_entree']],
            ],
            'Aide et assistant' => [
                'sentinelle' => 'Models/AideQuestion.php',
                'tables' => ['aide_questions' => ['source', 'besoin_support']],
            ],
            'Cours en direct' => [
                'sentinelle' => 'Models/ClasseVirtuelle.php',
                'tables' => [
                    'classes_virtuelles' => ['salle', 'debut_le', 'duree_minutes', 'filiere_id'],
                    'classe_presences' => ['classe_virtuelle_id', 'user_id'],
                ],
            ],

            'Atelier de code' => [
                 'sentinelle' => 'Models/Exercice.php',
                 'tables' => ['exercices' => ['langage', 'tests'], 'exercice_progressions' => ['reussi', 'tentatives']],
            ],
            'Nouveautés' => [
                 'sentinelle' => 'Models/Nouveaute.php',
                 'tables' => ['nouveautes' => ['type', 'lien'], 'users' => ['nouveautes_vues_at']],
            ],

            'Surveillance des paiements' => [
                'sentinelle' => 'Models/PaiementJournal.php',
                'tables' => ['paiement_journal' => ['type', 'message']],
            ],
        ];

        foreach ($groupes as $nom => $groupe) {
            if ($groupe['sentinelle'] !== null && ! file_exists(app_path($groupe['sentinelle']))) {
                $this->line("  <fg=gray>[--]</> {$nom} : non installé.");

                continue;
            }

            $manque = [];

            foreach ($groupe['tables'] as $table => $colonnes) {
                if (! Schema::hasTable($table)) {
                    $manque[] = "table {$table}";

                    continue;
                }

                foreach ($colonnes as $colonne) {
                    if (! Schema::hasColumn($table, $colonne)) {
                        $manque[] = "colonne {$table}.{$colonne}";
                    }
                }
            }

            if ($manque === []) {
                $this->ok("{$nom} : tables et colonnes présentes.");

                continue;
            }

            $conseil = 'Lance : php artisan migrate';

            if (Schema::hasTable('classe_virtuelles') || str_contains(implode(',', $manque), 'classes_virtuelles')) {
                $conseil = 'Crée et lance la migration de réparation « repare_tables_direct » puis : php artisan migrate';
            }

            $this->echec("{$nom} : manque ".implode(', ', array_slice($manque, 0, 5)).(count($manque) > 5 ? '…' : ''), $conseil);
        }
    }

    private function verifierRoutesAdmin(): void
    {
        $routeur = app('router');
        $total = 0;
        $sansProtection = [];

        foreach (Route::getRoutes() as $route) {
            if (! str_starts_with($route->uri(), 'admin')) {
                continue;
            }

            $total++;

            $middlewares = collect($routeur->gatherRouteMiddleware($route))
                ->map(fn ($m) => is_string($m) ? $m : get_class($m));

            $auth = $middlewares->contains(fn ($m) => str_starts_with($m, 'Illuminate\\Auth\\Middleware\\Authenticate'));
            $admin = $middlewares->contains(fn ($m) => str_starts_with($m, EnsureAdmin::class));

            if (! $auth || ! $admin) {
                $sansProtection[] = '/'.$route->uri();
            }
        }

        if ($total === 0) {
            $this->alerte('Aucune route d\'administration trouvée.');
        } elseif ($sansProtection === []) {
            $this->ok("{$total} routes d'administration protégées (connexion + rôle admin).");
        } else {
            $this->echec('Routes d\'administration SANS protection complète : '.implode(', ', array_slice($sansProtection, 0, 4)), 'Place-les dans un groupe Route::middleware([\'auth\', \'admin\']).');
        }
    }

    /** Simule un visiteur non connecté : il doit être renvoyé vers la page de connexion. */
    private function verifierVisiteur(): void
    {
        foreach (['/admin', '/admin/direct', '/tableau-de-bord', '/abonnement'] as $url) {
            try {
                $requete = Request::create($url, 'GET');
                $reponse = app(Kernel::class)->handle($requete);
                $destination = (string) $reponse->headers->get('Location', '');

                if ($reponse->getStatusCode() === 302 && str_contains($destination, '/connexion')) {
                    $this->ok("Visiteur sur {$url} : renvoyé vers la connexion.");
                } else {
                    $this->echec("Visiteur sur {$url} : code {$reponse->getStatusCode()} au lieu d'une redirection vers /connexion.", 'Vérifie routes/web.php et le middleware auth.');
                }
            } catch (Throwable $e) {
                $this->alerte("Test visiteur sur {$url} impossible : ".$e->getMessage());
            }
        }
    }

    private function verifierEnvironnement(): void
    {
        config('app.key') ? $this->ok('APP_KEY définie.') : $this->echec('APP_KEY manquante.', 'Lance : php artisan key:generate');

        is_writable(storage_path('logs')) && is_writable(base_path('bootstrap/cache'))
            ? $this->ok('storage et bootstrap/cache sont accessibles en écriture.')
            : $this->echec('storage ou bootstrap/cache non accessible en écriture.', 'Sous Linux : chmod -R 775 storage bootstrap/cache');

        if (file_exists(public_path('hot'))) {
            $this->alerte('Le fichier public/hot existe : si « npm run dev » n\'est pas lancé, les pages s\'affichent sans style. Supprime-le (del public\\hot) ou lance npm run build.');
        } elseif (file_exists(public_path('build/manifest.json'))) {
            $this->ok('Le front est compilé (public/build).');
        } else {
            $this->echec('Front non compilé.', 'Lance : npm run build');
        }

        if (app()->isProduction() && in_array(config('mail.default'), ['log', 'array'], true)) {
            $this->echec('MAIL_MAILER vaut « '.config('mail.default').' » en production : aucun e-mail n\'est envoyé (mot de passe oublié, alertes).', 'Configure un serveur SMTP dans .env (MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD…).');
        }

        if (! app()->isProduction()) {
            $this->line('  <fg=gray>[--]</> Clés externes (Anthropic, GeniusPay, JaaS, WhatsApp) : seulement vérifiées en production.');

            return;
        }

        foreach ([
            'aide.assistant.cle' => 'ANTHROPIC_API_KEY (coach et assistant d\'aide)',
            'aide.support.whatsapp' => 'SUPPORT_WHATSAPP (bouton de support)',
            'visio.jaas.app_id' => 'JAAS_APP_ID (cours en direct)',
        ] as $cle => $libelle) {
            if (config()->has($cle) && blank(config($cle))) {
                $this->alerte("{$libelle} est vide.");
            }
        }
    }

    private function section(string $titre): void
    {
        $this->newLine();
        $this->line("<options=bold>{$titre}</>");
    }

    private function ok(string $message): void
    {
        $this->line("  <fg=green>[OK]</> {$message}");
    }

    private function alerte(string $message): void
    {
        $this->alertes++;
        $this->line("  <fg=yellow>[!]</>  {$message}");
    }

    private function echec(string $message, string $conseil): void
    {
        $this->echecs++;
        $this->line("  <fg=red>[X]</>  {$message}");
        $this->line("       <fg=gray>→ {$conseil}</>");
    }
}