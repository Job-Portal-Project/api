<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GenerateJwtKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jwt:generate-keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate JWT RSA keys, set permissions, and update .env file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Ensure the storage/jwt directory exists
        $this->createJwtStorageDirectory();

        // Step 1: Generate the private key
        $this->generatePrivateKey();

        // Step 2: Generate the public key
        $this->generatePublicKey();

        // Step 3: Set group read permissions for the private key
        $this->setReadPermissions();

        // Step 4: Update the .env file with the real paths of the keys
        $this->updateEnvFile();

        return Command::SUCCESS;
    }

    /**
     * Ensure the storage/jwt directory exists.
     */
    private function createJwtStorageDirectory(): void
    {
        if (! File::exists(storage_path('jwt'))) {
            File::makeDirectory(storage_path('jwt'), 0755, true);
            $this->info('Created storage/jwt directory.');
        }
    }

    /**
     * Generate the RSA private key.
     */
    private function generatePrivateKey(): void
    {
        $process = new Process([
            'openssl',
            'genpkey',
            '-algorithm', 'RSA',
            '-out', storage_path('jwt/private_key.pem'),
            '-pkeyopt', 'rsa_keygen_bits:2048',
        ]);

        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->info('Private key generated successfully.');
    }

    /**
     * Generate the RSA public key.
     */
    private function generatePublicKey(): void
    {
        $process = new Process([
            'openssl',
            'rsa',
            '-pubout',
            '-in', storage_path('jwt/private_key.pem'),
            '-out', storage_path('jwt/public_key.pem'),
        ]);

        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->info('Public key generated successfully.');
    }

    /**
     * Set group read permissions for the private key.
     */
    private function setReadPermissions(): void
    {
        $privateKeyPath = storage_path('jwt/private_key.pem');

        // Set the file permissions to allow group read access
        chmod($privateKeyPath, 0644);

        $this->info('Group read permissions set for private key.');
    }

    /**
     * Update the .env file with the real paths of the private and public keys.
     */
    private function updateEnvFile(): void
    {
        $privateKeyPath = storage_path('jwt/private_key.pem');
        $publicKeyPath = storage_path('jwt/public_key.pem');

        $this->updateEnvVariable('JWT_PRIVATE_KEY', $privateKeyPath);
        $this->updateEnvVariable('JWT_PUBLIC_KEY', $publicKeyPath);

        $this->info('.env file updated with JWT key paths.');
    }

    /**
     * Update a specific environment variable in the .env file.
     */
    private function updateEnvVariable(string $key, string $value): void
    {
        $envFile = base_path('.env');
        $content = file_get_contents($envFile);

        if (strpos($content, "$key=") !== false) {
            // Update the existing key
            $content = preg_replace("/^$key=.*$/m", "$key=$value", $content);
        } else {
            // Add the key to the end of the file
            $content .= "\n$key=$value";
        }

        file_put_contents($envFile, $content);
    }
}
