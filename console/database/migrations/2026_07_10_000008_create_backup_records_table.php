<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBackupRecordsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('backup_records', function (Blueprint $table) {
            $table->id();
            $table->string('type', 32)->comment('备份类型：snapshot|full|incremental');
            $table->string('target', 128)->comment('备份目标：pg_dump|minio|config');
            $table->string('path', 512)->comment('备份文件路径');
            $table->bigInteger('size')->default(0)->comment('备份大小（字节）');
            $table->enum('status', ['running', 'completed', 'failed'])->default('running')->comment('状态');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->timestamps();
            
            $table->index(['target', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_records');
    }
}
