<?php

return [
    // Un paiement en attente depuis plus longtemps est vérifié directement auprès de GeniusPay
    'attente_minutes' => 10,

    // Un paiement en attente depuis plus longtemps est considéré comme bloqué
    'blocage_minutes' => 30,

    // Alerte « réseaux mobiles » : au moins N paiements bloqués ET aucun paiement réussi sur la période
    'blocage_minimum' => 3,
    'fenetre_heures' => 2,

    // Pas deux fois la même alerte dans cet intervalle
    'pause_alerte_minutes' => 60,
];