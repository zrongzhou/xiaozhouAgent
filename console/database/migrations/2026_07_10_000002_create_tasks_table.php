<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->comment('所属项目');
            $table->string('name', 128)->comment('任务名称');
            $table->string('type', 32)->comment('任务类型：prd|design|code|test|deploy');
            $table->enum('status', ['pending', 'running', 'review', 'done', 'failed', 'retry'])->default('pending')->comment('状态');
            $table->text('input')->comment('输入（自然语言/结构化）');
            $table->json('output')->nullable()->comment('输出（结构化产物）');
            $table->string('assigned_role', 32)->nullable()->comment('分配的角色（product|design|dev|qa|ops）');
            $table->unsignedBigInteger('assigned_user_id')->nullable()->comment('人工认领用户');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('父任务（用于子任务）');
            $table->string('model_profile', 32)->nullable()->comment('使用的模型画像');
            $table->float('cost_tokens', 8, 2)->default(0)->comment('消耗 token 数');
            $table->float('cost_ms', 10, 2)->default(0)->comment('耗时毫秒');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->index(['project_id', 'status']);
            $table->index(['type', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
}
