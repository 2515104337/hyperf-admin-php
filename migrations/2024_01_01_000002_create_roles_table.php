<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateRolesTable extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50)->comment('角色名称');
            $table->string('code', 50)->unique()->comment('角色代码');
            $table->string('description', 255)->default('')->comment('角色描述');
            $table->boolean('enabled')->default(true)->comment('是否启用');
            $table->integer('sort')->default(0)->comment('排序');
            $table->timestamps();
            $table->softDeletes();

            $table->index('enabled');
            $table->index('sort');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
}
