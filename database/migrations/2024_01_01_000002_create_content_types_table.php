<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('content_types', function (Blueprint $table) {
            $table->id();
            $table->string('display_name');
            $table->string('singular_name', 100);  // e.g. "article"
            $table->string('plural_name', 100);    // e.g. "articles" (API endpoint slug)
            $table->string('type', 20)->default('collection'); // collection | single
            $table->text('description')->nullable();
            $table->string('icon', 10)->nullable();  // emoji icon for sidebar
            $table->boolean('draft_publish')->default(true);
            $table->integer('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_types');
    }
};
