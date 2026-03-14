<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type', 20)->default('read-only'); // full-access | read-only | custom
            $table->string('token_hash', 64)->unique(); // SHA-256 of raw token
            $table->json('abilities')->nullable();       // {"articles":["find","findOne","create"],...} — only for custom type
            $table->unsignedSmallInteger('duration_days')->nullable(); // null = unlimited
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('api_tokens');
    }
};
