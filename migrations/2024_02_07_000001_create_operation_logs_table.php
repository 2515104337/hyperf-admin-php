<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateOperationLogsTable extends Migration
{
    public function up(): void
    {
        Schema::create('operation_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->comment('用户ID');
            $table->string('username', 100)->nullable()->comment('用户名');
            $table->string('method', 10)->comment('请求方法');
            $table->string('path', 500)->comment('请求路径');
            $table->text('params')->nullable()->comment('请求参数');
            $table->integer('status')->comment('响应状态码');
            $table->string('ip', 50)->nullable()->comment('IP地址');
            $table->string('user_agent', 500)->nullable()->comment('User Agent');
            $table->string('module', 100)->nullable()->comment('模块名称');
            $table->string('description', 255)->nullable()->comment('操作描述');
            $table->text('before_data')->nullable()->comment('操作前数据');
            $table->text('after_data')->nullable()->comment('操作后数据');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');

            $table->index('user_id');
            $table->index('created_at');
            $table->index('module');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_logs');
    }
}
