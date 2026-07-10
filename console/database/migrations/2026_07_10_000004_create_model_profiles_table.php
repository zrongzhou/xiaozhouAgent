<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelProfilesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('model_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64)->comment('模型画像名称');
            $table->string('slug', 32)->unique()->comment('唯一标识');
            $table->string('provider', 32)->comment('供应商：openai|anthropic|zhipu|gemini|local');
            $table->string('model', 64)->comment('模型标识');
            $table->string('base_url', 256)->nullable()->comment('API 地址');
            $table->string('api_key_encrypted')->nullable()->comment('加密的 API Key');
            $table->enum('tier', ['brain', 'light', 'backup'])->default('light')->comment('层级：主脑|轻量|备用');
            $table->json('capabilities')->nullable()->comment('能力标签（code|vision|long_context 等）');
            $table->float('cost_per_1k_input', 8, 6)->default(0)->comment('输入成本 /1K token');
            $table->float('cost_per_1k_output', 8, 6)->default(0)->comment('输出成本 /1K token');
            $table->integer('max_tokens')->default(4096)->comment('最大 token');
            $table->boolean('is_active')->default(true)->comment('是否启用');
            $table->timestamps();
            
            $table->index(['provider', 'tier']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_profiles');
    }
}
