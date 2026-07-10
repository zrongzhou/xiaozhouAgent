<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Project;

$project = Project::find(1);
if ($project) {
    echo "Project found: " . $project->name . "\n";
    
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
    
    echo "Team created: " . $team->name . "\n";
    echo "Team roles: " . json_encode($team->roles) . "\n";
} else {
    echo "Project not found\n";
}
