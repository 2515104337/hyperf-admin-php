<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateFileTable extends Migration
{
    public function up(): void
    {
        Schema::create('file', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cid')->default(0)->comment('分类ID');
            $table->tinyInteger('type')->default(10)->comment('类型: 10=图片, 20=视频, 30=文件');
            $table->string('name', 255)->comment('文件名称');
            $table->string('uri', 500)->comment('文件路径');
            $table->tinyInteger('source')->default(0)->comment('来源: 0=后台, 1=用户');
            $table->unsignedBigInteger('source_id')->default(0)->comment('来源ID');
            $table->timestamps();
            $table->softDeletes();

            $table->index('cid');
            $table->index('type');
            $table->index('source');
            $table->index('source_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file');
    }
}
