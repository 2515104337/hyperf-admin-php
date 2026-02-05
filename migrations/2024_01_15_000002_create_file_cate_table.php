<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateFileCateTable extends Migration
{
    public function up(): void
    {
        Schema::create('file_cate', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('pid')->default(0)->comment('父级ID');
            $table->tinyInteger('type')->default(10)->comment('类型: 10=图片, 20=视频, 30=文件');
            $table->string('name', 100)->comment('分类名称');
            $table->timestamps();
            $table->softDeletes();

            $table->index('pid');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_cate');
    }
}
