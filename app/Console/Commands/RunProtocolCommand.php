<?php

namespace App\Console\Commands;

use App\Protocols\ProtocolRegistry;
use Illuminate\Console\Command;

class RunProtocolCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'protocol:run
                            {protocol=level-1-budget-diagnostic : The protocol name or slug to run}
                            {--project= : Project ID}
                            {--component= : Component ID filter}
                            {--component_item= : Component Item ID filter}
                            {--json : Output raw JSON result}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute an MQMS system protocol manually.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $protocolIdentifier = $this->argument('protocol');
        $projectId = $this->option('project');

        if (!$projectId) {
            $projectId = $this->ask('Please enter the Project ID:');
        }

        if (!$projectId) {
            $this->error('Project ID is required.');
            return 1;
        }

        $params = [
            'project_id'        => (int) $projectId,
            'component_id'      => $this->option('component') ? (int) $this->option('component') : null,
            'component_item_id' => $this->option('component_item') ? (int) $this->option('component_item') : null,
        ];

        try {
            $result = ProtocolRegistry::run($protocolIdentifier, $params);
        } catch (\Exception $e) {
            $this->error("Failed to run protocol: " . $e->getMessage());
            return 1;
        }

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT));
            return $result['passed'] ? 0 : 2;
        }

        $this->info("==========================================");
        $this->info(" Protocol: " . ($result['protocol'] ?? $protocolIdentifier));
        $this->info(" Project:  #" . ($result['project_id'] ?? $projectId) . " " . ($result['project_name'] ?? ''));
        $this->info(" Status:   " . ($result['passed'] ? 'PASSED (Within Budget)' : 'FAILED (Budget Exceeded)'));
        $this->info("==========================================");
        $this->line($result['summary'] ?? '');

        if (!empty($result['violations'])) {
            $this->newLine();
            $this->warn("Violations (" . count($result['violations']) . "):");

            $tableRows = [];
            foreach ($result['violations'] as $v) {
                $tableRows[] = [
                    $v['component_item_id'],
                    $v['component_item_name'],
                    $v['component_name'] ?? '-',
                    $v['allowed_budget'] . ' ' . $v['unit_text'],
                    $v['total_requested'] . ' ' . $v['unit_text'],
                    '+' . $v['over_budget_quantity'] . ' ' . $v['unit_text'],
                ];
            }

            $this->table(
                ['Item ID', 'Item Name', 'Component', 'Allowed Budget', 'Requested (Actual)', 'Over Budget'],
                $tableRows
            );
        }

        return $result['passed'] ? 0 : 2;
    }
}
