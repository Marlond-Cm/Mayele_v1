<?php

return [
    // jaas  = Jitsi hébergé par 8x8
    // jwt   = ton propre serveur Jitsi avec authentification par jeton (recommandé si tu héberges)
    // ouvert = ton propre serveur Jitsi sans jeton (pas de contrôle des modérateurs)
    'mode' => env('VISIO_MODE', 'jaas'),

    'jaas' => [
        'app_id' => env('JAAS_APP_ID'),
        'cle_id' => env('JAAS_KEY_ID'),
        'cle_privee' => env('JAAS_PRIVATE_KEY_FILE', 'jaas.pk'), // fichier dans storage/app/private
    ],

    'serveur' => [
        'domaine' => env('JITSI_DOMAIN'),
        'app_id' => env('JITSI_APP_ID'),
        'secret' => env('JITSI_APP_SECRET'),
    ],

    // La salle ouvre X minutes avant le début et reste ouverte Y minutes après la fin
    'ouverture_avant_minutes' => 10,
    'fermeture_apres_minutes' => 60,
];