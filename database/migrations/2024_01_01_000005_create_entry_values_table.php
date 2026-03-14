<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('entry_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entry_id')->constrained()->onDelete('cascade');
            $table->foreignId('field_id')->constrained()->onDelete('cascade');
            $table->longText('value_text')->nullable();          // text, textarea, richtext, email, uid, password(hashed), enum
            $table->decimal('value_number', 20, 4)->nullable(); // number
            $table->tinyInteger('value_boolean')->nullable();    // boolean
            $table->timestamp('value_date')->nullable();         // date, datetime
            $table->json('value_json')->nullable();              // media [{id,url}], relation IDs [1,2,3], json
            $table->timestamps();

            $table->unique(['entry_id', 'field_id']);
            $table->index('entry_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('entry_values');
    }
};
