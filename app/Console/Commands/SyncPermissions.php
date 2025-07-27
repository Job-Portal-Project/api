<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-permissions';

    protected array $permissions = [
        'create',
        'update',
        'index',
        'view',
        'delete'
    ];

    private array $exceptedTables = [
        "password_reset_tokens",
        "failed_jobs",
        "password_access_tokens",
        "migrations",
        "job_batches",
        "cache_locks",
        "jobs",
        "sessions",
        "cache",
        "jwt_tokens",
        "jwt_token_blacklist",
        "personal_access_tokens",
        "model_has_permissions",
        "role_has_permissions",
        "model_has_roles",
        "model_info_permission",
        "model_infos"
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create permissions in a synchronized manner for all migrated tables and persist them into the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->components->info("Syncing permissions.");
        Artisan::call('permission:cache-reset');
        $this->call('app:reset-permissions');

        DB::transaction(function () {
            foreach($this->tables() as $table) {
                foreach($this->permissions as $permission) {
                    $this->components->task(
                        $permissionName = "$permission $table",
                        fn () => Permission::query()->create([
                            'name' => $permissionName,
                            'guard_name' => 'api'
                        ])
                    );
                }
            }
        });

        $this->components->success('All permissions have been synchronized.');
    }

    private function tables(): array
    {
        return array_filter(
            DB::table('information_schema.tables')
                ->where('table_schema', 'public')
                ->where('table_type', 'BASE TABLE')
                ->pluck('table_name')
                ->toArray(),

            fn (string $tableName) =>
                ! in_array($tableName, $this->exceptedTables)
                && !str_ends_with($tableName, '_translations')
        );
    }
}
