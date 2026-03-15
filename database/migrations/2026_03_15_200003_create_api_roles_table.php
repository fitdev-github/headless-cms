<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('api_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();          // 'Public', 'Authenticated'
            $table->string('description')->nullable();
            $table->boolean('is_default')->default(false);  // Public role is default
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('api_roles');
    }
};
