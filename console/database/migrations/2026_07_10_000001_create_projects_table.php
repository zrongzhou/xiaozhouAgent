<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128)->comment('项目名称');
            $table->string('slug', 64)->unique()->comment('项目标识（URL友好）');
            $table->text('description')->nullable()->comment('项目描述');
            $table->enum('status', ['draft', 'active', 'paused', 'completed', 'archived'])->default('draft')->comment('状态');
            $table->text('prompt')->comment('用户自然语言描述');
            $table->text('reference_files')->nullable()->comment('参考文件路径（JSON数组）');
            $table->text('reference_images')->nullable()->comment('参考图路径（JSON数组）');
            $table->string('style_preset', 32)->nullable()->comment('风格预设（来自 config/style-guide.yaml）');
            $table->json('config')->nullable()->comment('项目级配置覆盖');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
}
