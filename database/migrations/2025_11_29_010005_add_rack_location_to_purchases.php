<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRackLocationToPurchases extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('purchases', 'rack_location')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->string('rack_location')->nullable()->after('conversion_factor');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('purchases', 'rack_location')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropColumn('rack_location');
            });
        }
    }
}
