<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stockcountingtrans', function (Blueprint $table) {
            // Add waste tracking fields
            $table->decimal('WASTECOUNT', 28, 12)->nullable()->after('COUNTED');
            $table->string('WASTETYPE', 100)->nullable()->after('WASTECOUNT');
            $table->decimal('RECEIVEDCOUNT', 28, 12)->nullable()->after('WASTETYPE');
            
            // Add indexes for better performance
            $table->index(['WASTETYPE'], 'idx_wastetype');
            $table->index(['WASTECOUNT'], 'idx_wastecount');
            $table->index(['RECEIVEDCOUNT'], 'idx_receivedcount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stockcountingtrans', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_wastetype');
            $table->dropIndex('idx_wastecount');
            $table->dropIndex('idx_receivedcount');
            
            // Drop columns
            $table->dropColumn(['WASTECOUNT', 'WASTETYPE', 'RECEIVEDCOUNT']);
        });
    }
};
