<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'asker_name')) {
                $table->string('asker_name')->nullable()->after('asker_id');
            }
            if (!Schema::hasColumn('questions', 'admin_name')) {
                $table->string('admin_name')->nullable()->after('admin_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'asker_name')) {
                $table->dropColumn('asker_name');
            }
            if (Schema::hasColumn('questions', 'admin_name')) {
                $table->dropColumn('admin_name');
            }
        });
    }
};
