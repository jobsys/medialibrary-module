<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('library_medias', function (Blueprint $table) {
            $table->id();
            $table->integer('creator_id')->index()->comment('创建者');
            $table->integer('department_id')->index()->nullable()->default(0)->comment('所属部门');
            $table->string('name')->index()->comment('名称');
            $table->string('category')->index()->comment('类型');
            $table->string('extension')->index()->nullable()->comment('扩展名');
            $table->float('size')->nullable()->default(0)->comment('大小');
            $table->json('media')->comment('路径');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_medias');
    }
};
