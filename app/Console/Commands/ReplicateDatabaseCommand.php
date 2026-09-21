<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReplicateDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'replicate:db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Replicate all tables from a source database to the current application database, excluding migrations, sessions, and failed_jobs.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('=== Database Replication Tool ===');

        // 1. Gather Connection details with interactive prompts
        $defaultHost = config('database.connections.mysql.host', '127.0.0.1');
        $defaultPort = config('database.connections.mysql.port', '3306');
        
        $host = $this->ask('Please enter the source Database Host', $defaultHost);
        $port = $this->ask('Please enter the source Database Port', $defaultPort);
        $database = $this->ask('Please enter the source Database Name');
        $username = $this->ask('Please enter the source Database Username');
        $password = $this->secret('Please enter the source Database Password');

        if (empty($database) || empty($username)) {
            $this->error('Database Name and Username are strictly required to proceed.');
            return 1;
        }

        // 2. Setup dynamic source connection configuration
        config([
            'database.connections.source_db' => [
                'driver' => 'mysql',
                'host' => $host,
                'port' => $port,
                'database' => $database,
                'username' => $username,
                'password' => $password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'prefix_indexes' => true,
                'strict' => true,
                'engine' => null,
            ]
        ]);

        $this->info("Connecting to source database `{$database}`...");

        // Test the source database connection & fetch table list
        try {
            $tablesResult = DB::connection('source_db')->select('SHOW TABLES');
        } catch (\Exception $e) {
            $this->error('Failed to establish a connection to the source database: ' . $e->getMessage());
            return 1;
        }

        if (empty($tablesResult)) {
            $this->warn('Source database is empty. No tables found.');
            return 0;
        }

        // Extract raw table names from the object results
        $allTables = [];
        foreach ($tablesResult as $row) {
            $rowArray = (array) $row;
            $allTables[] = reset($rowArray);
        }

        // Filter out excluded tables
        $exclude = ['migrations', 'sessions', 'failed_jobs', 'migration', 'session'];
        $tablesToReplicate = array_filter($allTables, function ($table) use ($exclude) {
            return !in_array(strtolower($table), $exclude);
        });

        if (empty($tablesToReplicate)) {
            $this->warn('No tables left to replicate after applying exclusion filters.');
            return 0;
        }

        $this->info('Excluding system tables: ' . implode(', ', $exclude));
        $this->info('Disabling foreign key checks on local database...');

        // Disable foreign key checks locally
        try {
            DB::connection()->statement('SET FOREIGN_KEY_CHECKS=0');
        } catch (\Exception $e) {
            try {
                DB::connection()->statement('PRAGMA foreign_keys = OFF');
            } catch (\Exception $ex) {
                $this->warn('Could not disable foreign key checks: ' . $e->getMessage());
            }
        }

        $this->info('Starting replication of ' . count($tablesToReplicate) . ' tables...');

        foreach ($tablesToReplicate as $table) {
            $this->comment("--------------------------------------------------");
            $this->info("Processing table: `{$table}`");

            try {
                // 1. Fetch exact CREATE TABLE SQL from source
                $createResult = DB::connection('source_db')->select("SHOW CREATE TABLE `{$table}`");
                if (empty($createResult)) {
                    $this->error("Failed to retrieve schema for table `{$table}`.");
                    continue;
                }
                
                $createSqlRow = (array) $createResult[0];
                $createSql = $createSqlRow['Create Table'] ?? null;

                if (!$createSql) {
                    $this->error("Could not parse schema definition for table `{$table}`.");
                    continue;
                }

                // 2. Recreate schema locally
                $this->comment("Recreating table structure locally for `{$table}`...");
                DB::connection()->statement("DROP TABLE IF EXISTS `{$table}`");
                DB::connection()->statement($createSql);

                // 3. Replicate Data in Chunks
                $totalRows = DB::connection('source_db')->table($table)->count();
                if ($totalRows > 0) {
                    $this->info("Copying {$totalRows} records...");
                    $progressBar = $this->output->createProgressBar($totalRows);
                    $progressBar->start();

                    DB::connection('source_db')->table($table)->orderByRaw('1')->chunk(1000, function ($rows) use ($table, $progressBar) {
                        $insertData = array_map(function ($row) {
                            return (array) $row;
                        }, $rows->toArray());

                        DB::connection()->table($table)->insert($insertData);
                        $progressBar->advance(count($insertData));
                    });

                    $progressBar->finish();
                    $this->newLine();
                } else {
                    $this->info("Table `{$table}` has no records. Schema created successfully.");
                }

            } catch (\Exception $e) {
                $this->error("Error while processing table `{$table}`: " . $e->getMessage());
            }
        }

        $this->comment("--------------------------------------------------");
        $this->info('Re-enabling foreign key checks...');

        // Re-enable foreign key checks locally
        try {
            DB::connection()->statement('SET FOREIGN_KEY_CHECKS=1');
        } catch (\Exception $e) {
            try {
                DB::connection()->statement('PRAGMA foreign_keys = ON');
            } catch (\Exception $ex) {}
        }

        $this->info('Purging temporary configuration...');
        config()->offsetUnset('database.connections.source_db');

        $this->info('Database replication completed successfully!');
        return 0;
    }
}
