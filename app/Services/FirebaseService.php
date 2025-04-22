<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class FirebaseService
{
    protected Database $database;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(
            config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'));
        
        $this->database = $factory->createDatabase();
        
    }

    // Actualiza cualquier ruta en Firebase
    public function updateDataRealTime(string $path, object|array $data): void
    {
        $this->database
            ->getReference($path)
            ->set($data);
    }
}