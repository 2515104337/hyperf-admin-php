<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class AddMenuExtraFields extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->boolean('show_badge')->default(false)->comment('是否显示徽章')->after('is_affix');
            $table->string('show_text_badge', 50)->default('')->comment('文本徽章内容')->after('show_badge');
            $table->boolean('is_full_page')->default(false)->comment('是否全屏页面')->after('show_text_badge');
            $table->string('active_path', 255)->default('')->comment('激活路径')->after('is_full_page');
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn(['show_badge', 'show_text_badge', 'is_full_page', 'active_path']);
        });
    }
}
