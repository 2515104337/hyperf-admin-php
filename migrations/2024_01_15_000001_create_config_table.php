<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateConfigTable extends Migration
{
    public function up(): void
    {
        Schema::create('config', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type', 50)->comment('配置类型');
            $table->string('name', 100)->comment('配置名称');
            $table->text('value')->nullable()->comment('配置值(JSON)');
            $table->timestamps();

            $table->unique(['type', 'name']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('config');
    }
}
