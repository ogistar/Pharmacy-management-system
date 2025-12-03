<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompoundToPrescriptionItems extends Migration
{
    public function up()
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            $table->foreignId('compound_id')->nullable()->after('product_id')->constrained('compounds')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            if (Schema::hasColumn('prescription_items', 'compound_id')) {
                $table->dropForeign(['compound_id']);
                $table->dropColumn('compound_id');
            }
        });
    }
}
