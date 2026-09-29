<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A user config (értesítendők, max_events) a user Drive-ján lévő config.json-ba költözött, a böngésző maga kér
 * Google tokent, és mindenki csak a saját eseményeit látja – ezeket az adatokat a backend már nem tárolja.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('user_allowed_emails');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'google_refresh_token',
                'drive_folder_id',
                'notification_emails',
                'notification_phones',
                'max_events',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('google_refresh_token')->nullable();
            $table->string('drive_folder_id')->nullable();
            $table->string('notification_emails', 255)->nullable();
            $table->string('notification_phones', 255)->nullable();
            $table->unsignedInteger('max_events')->default(100);
        });

        Schema::create('user_allowed_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('email', 128)->index();
            $table->timestamps();

            $table->unique(['user_id', 'email']);
        });
    }
};
