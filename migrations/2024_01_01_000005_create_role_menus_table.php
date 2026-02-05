<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateRoleMenusTable extends Migration
{
    public function up(): void
    {
        Schema::create('role_menus', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->comment('角色ID');
            $table->unsignedBigInteger('menu_id')->comment('菜单ID');

            $table->primary(['role_id', 'menu_id']);
            $table->index('menu_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_menus');
    }
}
