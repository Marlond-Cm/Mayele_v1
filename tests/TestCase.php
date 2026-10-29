<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // Sécurité : RefreshDatabase efface toutes les tables de la base utilisée.
        $base = (string) ($_ENV['DB_DATABASE'] ?? (getenv('DB_DATABASE') ?: ''));

        if (! str_ends_with($base, '_test')) {
            throw new RuntimeException("Tests refusés : la base « {$base} » ne finit pas par _test. Vérifie le bloc <php> de phpunit.xml.");
        }

        parent::setUp();

        $this->withoutVite();
    }
}