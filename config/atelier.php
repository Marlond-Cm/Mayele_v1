<?php

return [
    // Temps maximum d'exécution d'un programme d'étudiant (millisecondes)
    'delai_execution_ms' => 4000,

    // Bibliothèques chargées dans le navigateur au besoin. Si une version ne se charge plus
    // (F12 > Console), mets à jour le numéro de version ici.
    'pyodide_url' => 'https://cdn.jsdelivr.net/pyodide/v0.28.0/full/pyodide.js',
    'sqljs_base' => 'https://cdn.jsdelivr.net/npm/sql.js@1.10.3/dist/',
];