<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MigrateCommand extends Command
{
    protected $signature = 'xcvm:migrate {--fresh : Drop all tables and re-run} {--seed : Run seeders after migration}';
    protected $description = 'Run XC_VM database migrations with optional seed';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            if (!$this->confirm('This will DROP all tables. Continue?')) return 1;
            $this->warn('Dropping all tables...');
            Artisan::call('migrate:fresh', ['--force' => true]);
        } else {
            Artisan::call('migrate', ['--force' => true]);
        }

        $this->info(Artisan::output());

        if ($this->option('seed')) {
            $this->info('Seeding database...');
            Artisan::call('db:seed', ['--force' => true]);
            $this->info(Artisan::output());
        }

        $this->info('Migration complete.');
        return 0;
    }
}
