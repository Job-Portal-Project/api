<?php

namespace App\Console\Commands;

use App\Models\ModelInfo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-models';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->components->info('Resetting models.');

        DB::statement('SET session_replication_role = replica;');
        ModelInfo::query()->truncate();
        DB::statement('SET session_replication_role = DEFAULT;');
    }
}
