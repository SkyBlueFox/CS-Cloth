<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('order_items', 'refund_evidence_image_path')) {
            return;
        }

        Schema::table('order_items', function (Blueprint $table) {
            $table->text('refund_evidence_image_path')->nullable()->after('refund_issue_description');
        });
    }

    public function down(): void
    {
        // This column may already belong to the base order_items migration.
    }
};
