<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('filename');        // stored filename (hashed)
            $table->string('original_name');   // original upload name
            $table->string('mime_type', 100);
            $table->unsignedInteger('size');   // bytes
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->string('path', 500);       // relative storage path
            $table->text('alt')->nullable();
            $table->text('caption')->nullable();
            $table->string('folder', 255)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('media');
    }
};
