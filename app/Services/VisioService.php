<?php

namespace App\Services;

use App\Models\ClasseVirtuelle;
use App\Models\User;
use RuntimeException;

class VisioService
{
    public function configure(): bool
    {
        return match (config('visio.mode')) {
            'jaas' => filled(config('visio.jaas.app_id'))
                && filled(config('visio.jaas.cle_id'))
                && is_file($this->cheminCle()),
            'jwt' => filled(config('visio.serveur.domaine'))
                && filled(config('visio.serveur.app_id'))
                && filled(config('visio.serveur.secret')),
            'ouvert' => filled(config('visio.serveur.domaine')),
            default => false,
        };
    }

    /**
     * Options passées à l'API Jitsi côté navigateur.
     *
     * @return array{domain: string, script: string, room: string, jwt: ?string, nom: string, sujet: string, moderateur: bool, audio: bool}
     */
    public function options(ClasseVirtuelle $classe, User $user, bool $moderateur, bool $audio): array
    {
        $commun = [
            'nom' => $user->name,
            'sujet' => $classe->titre,
            'moderateur' => $moderateur,
            'audio' => $audio,
        ];

        if (config('visio.mode') === 'jaas') {
            $app = (string) config('visio.jaas.app_id');

            return $commun + [
                'domain' => '8x8.vc',
                'script' => "https://8x8.vc/{$app}/external_api.js",
                'room' => "{$app}/{$classe->salle}",
                'jwt' => $this->jetonJaas($classe, $user, $moderateur),
            ];
        }

        $domaine = (string) config('visio.serveur.domaine');

        return $commun + [
            'domain' => $domaine,
            'script' => "https://{$domaine}/external_api.js",
            'room' => $classe->salle,
            'jwt' => config('visio.mode') === 'jwt' ? $this->jetonServeur($classe, $user, $moderateur) : null,
        ];
    }

    private function jetonJaas(ClasseVirtuelle $classe, User $user, bool $moderateur): string
    {
        $app = (string) config('visio.jaas.app_id');
        $kid = (string) config('visio.jaas.cle_id');

        // Le Key ID complet a la forme « App ID / identifiant de clé »
        if (! str_contains($kid, '/')) {
            $kid = $app.'/'.$kid;
        }

        $charge = [
            'aud' => 'jitsi',
            'iss' => 'chat',
            'sub' => $app,
            'room' => '*', // le nom de la salle est secret et le jeton n'est remis qu'aux personnes autorisées
            'nbf' => time() - 30,
            'exp' => $this->expiration($classe),
            'context' => [
                // Seuls l'identifiant et le nom sont transmis (pas l'e-mail)
                'user' => [
                    'id' => (string) $user->id,
                    'name' => $user->name,
                    'moderator' => $moderateur ? 'true' : 'false',
                ],
                'features' => [
                    'livestreaming' => false,
                    'recording' => false,
                    'transcription' => false,
                    'outbound-call' => false,
                ],
            ],
        ];

        return $this->signer(['alg' => 'RS256', 'kid' => $kid, 'typ' => 'JWT'], $charge, 'RS256');
    }

    private function jetonServeur(ClasseVirtuelle $classe, User $user, bool $moderateur): string
    {
        $app = (string) config('visio.serveur.app_id');

        $charge = [
            'iss' => $app,
            'aud' => $app,
            'sub' => (string) config('visio.serveur.domaine'),
            'room' => $classe->salle,
            'nbf' => time() - 30,
            'exp' => $this->expiration($classe),
            'context' => [
                'user' => [
                    'id' => (string) $user->id,
                    'name' => $user->name,
                    'moderator' => $moderateur,
                ],
            ],
        ];

        return $this->signer(['alg' => 'HS256', 'typ' => 'JWT'], $charge, 'HS256');
    }

    private function expiration(ClasseVirtuelle $classe): int
    {
        return max($classe->fin_le->copy()->addHours(2)->timestamp, time() + 3600);
    }

    private function signer(array $entete, array $charge, string $algorithme): string
    {
        $b64 = fn (string $donnees): string => rtrim(strtr(base64_encode($donnees), '+/', '-_'), '=');
        $json = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

        $contenu = $b64(json_encode($entete, $json)).'.'.$b64(json_encode($charge, $json));

        if ($algorithme === 'RS256') {
            $cle = openssl_pkey_get_private((string) file_get_contents($this->cheminCle()));

            if ($cle === false || ! openssl_sign($contenu, $signature, $cle, OPENSSL_ALGO_SHA256)) {
                throw new RuntimeException('Signature du jeton de visioconférence impossible : vérifie le fichier de clé privée.');
            }
        } else {
            $signature = hash_hmac('sha256', $contenu, (string) config('visio.serveur.secret'), true);
        }

        return $contenu.'.'.$b64($signature);
    }

    private function cheminCle(): string
    {
        return storage_path('app/private/'.basename((string) config('visio.jaas.cle_privee')));
    }
}