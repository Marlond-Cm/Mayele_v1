<?php

return [
    'devise' => env('PAIEMENT_DEVISE', 'XAF'),

    // Parrainage : jours offerts au parrain quand son filleul paie pour la 1re fois
    'parrainage_jours' => 30,

    // Annales : jours offerts par annale déposée puis validée (plafond par étudiant)
    'annale_jours' => 7,
    'annale_max' => 12,

    // Coach IA
    'coach' => [
        'cle' => env('ANTHROPIC_API_KEY'),
        'modele' => env('ANTHROPIC_MODEL', 'claude-haiku-5-5'),
        'version_api' => '2023-06-01',
        'messages_par_jour' => 20,   // par étudiant
        'max_caracteres' => 1200,    // taille max d'un message étudiant
        'max_tokens' => 900,         // taille max d'une réponse du coach
        'historique' => 10,          // messages renvoyés à l'IA (5 échanges)
        'contexte_max' => 12000,     // caractères du chapitre envoyés comme référence
    ],
];