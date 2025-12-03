<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFullPackFlagsToPrescriptionItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            if (!Schema::hasColumn('prescription_items', 'is_full_pack')) {
                $table->boolean('is_full_pack')->default(false)->after('label_note');
            }
        });

        Schema::table('prescription_item_components', function (Blueprint $table) {
            if (!Schema::hasColumn('prescription_item_components', 'is_full_pack')) {
                $table->boolean('is_full_pack')->default(false)->after('quantity');
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
        Schema::table('prescription_items', function (Blueprint $table) {
            if (Schema::hasColumn('prescription_items', 'is_full_pack')) {
                $table->dropColumn('is_full_pack');
            }
        });

        Schema::table('prescription_item_components', function (Blueprint $table) {
            if (Schema::hasColumn('prescription_item_components', 'is_full_pack')) {
                $table->dropColumn('is_full_pack');
            }
        });
    }
}
