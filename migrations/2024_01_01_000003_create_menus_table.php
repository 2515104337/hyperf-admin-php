<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateMenusTable extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('parent_id')->default(0)->comment('父级ID');
            $table->string('name', 50)->comment('路由名称');
            $table->string('path', 255)->comment('路由路径');
            $table->string('component', 255)->default('')->comment('组件路径');
            $table->string('redirect', 255)->default('')->comment('重定向路径');
            $table->string('title', 100)->comment('菜单标题');
            $table->string('icon', 50)->default('')->comment('菜单图标');
            $table->boolean('is_hide')->default(false)->comment('是否隐藏');
            $table->boolean('is_hide_tab')->default(false)->comment('是否隐藏标签页');
            $table->string('link', 255)->default('')->comment('外部链接');
            $table->boolean('is_iframe')->default(false)->comment('是否iframe');
            $table->boolean('keep_alive')->default(false)->comment('是否缓存');
            $table->boolean('is_affix')->default(false)->comment('是否固定标签页');
            $table->tinyInteger('type')->default(1)->comment('类型: 1目录 2菜单 3按钮');
            $table->string('permission', 100)->default('')->comment('权限标识');
            $table->integer('sort')->default(0)->comment('排序');
            $table->boolean('enabled')->default(true)->comment('是否启用');
            $table->timestamps();
            $table->softDeletes();

            $table->index('parent_id');
            $table->index('type');
            $table->index('enabled');
            $table->index('sort');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
}
