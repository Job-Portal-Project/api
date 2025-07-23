<?php

namespace App\Console\Commands;

use App\Enums\JWT\TokenType;
use App\Models\JWT\Token;
use Illuminate\Console\Command;

class ClearExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-expired-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired tokens from the database';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $now = now();

        // Get the TTL for each token type from the configuration
        $tokenTypes = [
            TokenType::ACCESS->value => config('jwt.access.ttl'),
            TokenType::REFRESH->value => config('jwt.refresh.ttl'),
        ];

        // Prepare a query to delete expired tokens
        foreach ($tokenTypes as $type => $ttl) {
            $expiryDate = $now->subMinutes($ttl);

            // Delete expired tokens in bulk
            Token::query()
                ->where('type', $type)
                ->where('created_at', '<=', $expiryDate)
                ->delete();
        }
    }
}
