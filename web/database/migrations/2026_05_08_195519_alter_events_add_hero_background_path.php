<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('hero_background_path')->nullable()->after('image_path');
            $table->foreignId('parent_event_id')->nullable()->constrained('events')->onDelete('set null')->after('hero_background_path');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['parent_event_id']);
            $table->dropColumn(['hero_background_path', 'parent_event_id']);
        });
    }
};
