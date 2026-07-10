<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelRecordsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('model_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profile_id')->comment('模型画像');
            $table->string('task_type', 32)->comment('任务类型');
            $table->float('tokens_input', 10, 2)->default(0)->comment('输入 token');
            $table->float('tokens_output', 10, 2)->default(0)->comment('输出 token');
            $table->float('cost_usd', 10, 6)->default(0)->comment('成本（USD）');
            $table->float('duration_ms', 12, 2)->default(0)->comment('耗时毫秒');
            $table->boolean('success')->default(true)->comment('是否成功');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->timestamps();
            
            $table->foreign('profile_id')->references('id')->on('model_profiles')->onDelete('cascade');
            $table->index(['profile_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_records');
    }
}
