<?php

namespace App\Console\Commands;

use App\Models\ModelInfo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class SaveModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:save-models';

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
        $this->components->info('Saving models.');

        $this->call('app:reset-models');

        DB::transaction(function () {
            foreach ($this->models() as $model) {
                $this->components->task($model, function () use ($model) {
                    $modelInfo = ModelInfo::query()->create(['name' => $model]);
                    $modelInstance = new $model;
                    $tableName = $modelInstance->getTable();
                    $permissions = Permission::query()->where('name', 'like', "$tableName%")->get();
                    $modelInfo->permissions()->sync($permissions);
                });
            }
        });

        $this->components->success('Models saved successfully.');
    }

    private function models(): array
    {
        return array_filter(array_map(
            fn ($file) => str_replace(['.php', app_path(), DIRECTORY_SEPARATOR], ['', '\\App', '\\'], $file),
            glob(app_path('Models/*.php'))
        ), fn ($file) => ! str_contains($file, 'Translation'));
    }
}
