<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The unique (document_id, user_id) index cannot serve "what is shared with me"
 * lookups, which filter on user_id alone. SQLite does not index foreign keys.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_shares', function (Blueprint $table) {
            $table->index(['user_id', 'document_id']);
        });
    }

    public function down(): void
    {
        Schema::table('document_shares', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'document_id']);
        });
    }
};
