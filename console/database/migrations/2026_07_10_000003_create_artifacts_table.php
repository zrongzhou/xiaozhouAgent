<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArtifactsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('artifacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id')->comment('关联任务');
            $table->unsignedBigInteger('project_id')->comment('所属项目');
            $table->string('type', 32)->comment('产物类型：prd|design-spec|code|qa-report|deploy-config');
            $table->string('name', 128)->comment('产物名称');
            $table->string('path', 512)->comment('文件路径（MinIO/本地）');
            $table->string('mime_type', 64)->nullable()->comment('MIME 类型');
            $table->bigInteger('size')->default(0)->comment('文件大小（字节）');
            $table->json('metadata')->nullable()->comment('元数据（版本/校验/签名等）');
            $table->integer('version')->default(1)->comment('版本号');
            $table->boolean('is_latest')->default(true)->comment('是否最新版本');
            $table->timestamps();
            
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->unique(['task_id', 'name', 'version']);
            $table->index(['project_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artifacts');
    }
}
