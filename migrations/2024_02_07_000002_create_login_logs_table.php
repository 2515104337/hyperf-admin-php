<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateLoginLogsTable extends Migration
{
    public function up(): void
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->comment('用户ID');
            $table->string('username', 100)->comment('用户名');
            $table->string('action', 20)->comment('操作: login/logout');
            $table->string('status', 20)->comment('状态: success/failed');
            $table->string('failure_reason', 255)->nullable()->comment('失败原因');
            $table->string('ip', 50)->nullable()->comment('IP地址');
            $table->string('user_agent', 500)->nullable()->comment('User Agent');
            $table->string('browser', 100)->nullable()->comment('浏览器');
            $table->string('os', 100)->nullable()->comment('操作系统');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');

            $table->index('user_id');
            $table->index('created_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
}
