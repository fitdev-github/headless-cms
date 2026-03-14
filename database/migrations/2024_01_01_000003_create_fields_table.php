<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_type_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);         // field key slug (e.g. "firstName", "body")
            $table->string('display_name');      // label shown in admin
            $table->string('type', 50);          // text|textarea|richtext|number|boolean|date|datetime|email|password|enumeration|uid|media|json|relation
            $table->json('options')->nullable(); // {required, unique, min, max, minLength, maxLength, default, private, enum_values:[], uid_field, relation_type_id, relation_kind:oneToMany|manyToMany, relation_display_field}
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fields');
    }
};
