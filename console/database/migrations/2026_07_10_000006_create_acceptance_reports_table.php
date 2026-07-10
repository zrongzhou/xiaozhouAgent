<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcceptanceReportsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('acceptance_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id')->comment('关联任务');
            $table->unsignedBigInteger('project_id')->comment('所属项目');
            $table->enum('status', ['pending', 'passed', 'failed', 'retry'])->default('pending')->comment('验收状态');
            $table->float('score_structure', 4, 2)->nullable()->comment('结构分（DOM）');
            $table->float('score_layout', 4, 2)->nullable()->comment('布局分（IoU）');
            $table->float('score_visual', 4, 2)->nullable()->comment('视觉分（CLIP）');
            $table->float('score_interaction', 4, 2)->nullable()->comment('交互分（E2E）');
            $table->float('score_total', 4, 2)->nullable()->comment('总分');
            $table->json('details')->nullable()->comment('详细比对数据');
            $table->text('human_notes')->nullable()->comment('人工备注');
            $table->timestamps();
            
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->index(['project_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acceptance_reports');
    }
}
