<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Override createApplication UNTUK MEMAKSA environment test SEBELUM aplikasi boot.
     *
     * Mengapa perlu: Docker Compose meng-export DB_CONNECTION=mysql ke $_SERVER.
     * Laravel `Env` membaca $_SERVER lebih dulu (variables_order=EGPCS) dan
     * atribut force="true" pada phpunit.xml hanya mengisi $_ENV/putenv, BUKAN $_SERVER.
     * Tanpa override ini, config database.default tetap 'mysql' dan RefreshDatabase
     * akan menghancurkan database produksi. Lihat INCIDENT INS-01.
     */
    public function createApplication()
    {
        $this->forceTestEnvironment();

        return parent::createApplication();
    }

    /**
     * Paksa variabel environment test pada $_ENV, $_SERVER, dan putenv sekaligus.
     */
    private function forceTestEnvironment(): void
    {
        $variables = [
            'APP_ENV' => 'testing',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'DB_URL' => '',
        ];

        foreach ($variables as $key => $value) {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("{$key}={$value}");
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->guardDatabaseSafety();
    }

    /**
     * GUARD KESELAMATAN DATA (wajib — lihat INCIDENT INS-01).
     *
     * Bila konfigurasi koneksi bukan sqlite in-memory atau environment bukan "testing",
     * test DIHENTIKAN dengan error keras SEBELUM menyentuh database apa pun.
     */
    private function guardDatabaseSafety(): void
    {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");
        $env = app()->environment();

        $safeConnection = $connection === 'sqlite' && $database === ':memory:';
        $safeEnv = $env === 'testing';

        if (! $safeConnection || ! $safeEnv) {
            $message = sprintf(
                "DILARANG menjalankan test destruktif: koneksi='%s', database='%s', env='%s'. ".
                'Test harus memakai sqlite :memory: pada APP_ENV=testing. '.
                'Lihat testing/bug-reports/INCIDENT-INS-01-database-data-loss.md',
                (string) $connection,
                (string) $database,
                (string) $env,
            );

            fwrite(STDERR, "\n[SAFETY-GUARD] {$message}\n");

            $this->fail($message);
        }
    }
}
