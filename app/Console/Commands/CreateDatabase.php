<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:database {dbname}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creating default database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $dbname = $this->argument('dbname');

            $hasDb = DB::connection()->select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" .$dbname . "'");

            if (empty($hasDb)) {
                DB::connection()->statement('CREATE DATABASE ' . $dbname);
                $this->info('Database "'. $dbname . '" created!');
            }
            else {
                $this->info('Database "' . $dbname . '" already exists...!');
            }
        }
        catch (\Exception $e){
            $this->error($e->getMessage());
        }
    }
}
