<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->comment('所属项目');
            $table->string('name', 64)->comment('团队名称');
            $table->enum('topology', ['hierarchical', 'pipeline', 'parallel', 'debate'])->default('pipeline')->comment('协作拓扑');
            $table->json('roles')->comment('角色配置（JSON，含模型画像）');
            $table->json('blackboard')->nullable()->comment('共享黑板内容');
            $table->timestamps();
            
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->index('project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
}
