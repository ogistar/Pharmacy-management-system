<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompoundFieldsToPrescriptionItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            $table->boolean('is_controlled')->default(false)->after('dosage');
            $table->boolean('is_compound')->default(false)->after('is_controlled');
            $table->text('compound_note')->nullable()->after('is_compound');
            $table->text('label_note')->nullable()->after('compound_note');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            $table->dropColumn(['is_controlled','is_compound','compound_note','label_note']);
        });
    }
}
