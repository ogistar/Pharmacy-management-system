<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWorkflowColumnsToPrescriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('prescriptions', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('prescriptions', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('prescriptions', 'dispensed_by')) {
                $table->foreignId('dispensed_by')->nullable()->after('approved_at')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('prescriptions', 'dispensed_at')) {
                $table->timestamp('dispensed_at')->nullable()->after('dispensed_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            if (Schema::hasColumn('prescriptions', 'approved_by')) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn(['approved_by']);
            }
            if (Schema::hasColumn('prescriptions', 'approved_at')) {
                $table->dropColumn(['approved_at']);
            }
            if (Schema::hasColumn('prescriptions', 'dispensed_by')) {
                $table->dropForeign(['dispensed_by']);
                $table->dropColumn(['dispensed_by']);
            }
            if (Schema::hasColumn('prescriptions', 'dispensed_at')) {
                $table->dropColumn(['dispensed_at']);
            }
        });
    }
}
