<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add localized toggle to content types
        Schema::table('content_types', function (Blueprint $table) {
            $table->boolean('localized')->default(false)->after('draft_publish');
        });

        // Add localizable toggle to fields (per-field control)
        Schema::table('fields', function (Blueprint $table) {
            $table->boolean('localizable')->default(true)->after('options');
        });

        // Add locale + locale_group_id to entries
        Schema::table('entries', function (Blueprint $table) {
            $table->string('locale', 10)->nullable()->after('status');
            $table->unsignedBigInteger('locale_group_id')->nullable()->after('locale');
            $table->index('locale_group_id', 'entries_locale_group_idx');
        });

        // Seed default locale settings (skip if already set)
        DB::table('settings')->insertOrIgnore([
            ['key' => 'locales',        'value' => '["en"]'],
            ['key' => 'default_locale', 'value' => 'en'],
        ]);
    }

    public function down(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->dropIndex('entries_locale_group_idx');
            $table->dropColumn(['locale', 'locale_group_id']);
        });

        Schema::table('fields', function (Blueprint $table) {
            $table->dropColumn('localizable');
        });

        Schema::table('content_types', function (Blueprint $table) {
            $table->dropColumn('localized');
        });
    }
};
