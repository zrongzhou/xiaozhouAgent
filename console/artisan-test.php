<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Project;

try {
    $project = Project::find(1);
    if ($project) {
        echo "Project found: " . $project->name . PHP_EOL;
        
        // Create team
        $team = $project->teams()->create([
            'name' => 'Test Team',
            'topology' => 'pipeline',
            'roles' => [
                ['slug' => 'dev', 'name' => 'Developer'],
                ['slug' => 'design', 'name' => 'Designer'],
            ],
            'blackboard' => [],
        ]);
        
        echo "Team created: " . $team->name . PHP_EOL;
        echo "Team roles: " . json_encode($team->roles) . PHP_EOL;
    } else {
        echo "Project not found" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
