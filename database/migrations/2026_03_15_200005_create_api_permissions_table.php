<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('api_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('api_roles')->cascadeOnDelete();
            $table->string('subject', 100);   // content type plural_name, 'upload', or '*'
            $table->string('action', 50);     // find|findOne|create|update|delete|upload.find|upload.findOne|upload.upload|upload.delete
            $table->boolean('enabled')->default(false);
            $table->timestamps();

            $table->unique(['role_id', 'subject', 'action']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('api_permissions');
    }
};
