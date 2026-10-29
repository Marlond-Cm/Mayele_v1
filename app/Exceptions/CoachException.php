<?php

namespace App\Exceptions;

use Exception;

/** Erreur du coach avec un message lisible par l'étudiant. */
class CoachException extends Exception
{
    public function __construct(string $message, public int $statut = 503)
    {
        parent::__construct($message);
    }
}