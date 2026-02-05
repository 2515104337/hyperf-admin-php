<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateAdminUsersTable extends Migration
{
    public function up(): void
    {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username', 50)->unique()->comment('用户名');
            $table->string('password', 255)->comment('密码');
            $table->string('nickname', 50)->default('')->comment('昵称');
            $table->string('avatar', 255)->default('')->comment('头像');
            $table->string('email', 100)->default('')->comment('邮箱');
            $table->string('phone', 20)->default('')->comment('手机号');
            $table->enum('gender', ['male', 'female', 'unknown'])->default('unknown')->comment('性别');
            $table->tinyInteger('status')->default(1)->comment('状态: 1启用 2禁用');
            $table->string('created_by', 50)->default('')->comment('创建人');
            $table->string('updated_by', 50)->default('')->comment('更新人');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_users');
    }
}
