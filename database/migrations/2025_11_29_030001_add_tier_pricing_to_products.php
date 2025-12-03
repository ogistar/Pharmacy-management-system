<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddTierPricingToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price_retail', 12, 2)->nullable()->after('price');
            $table->decimal('price_wholesale', 12, 2)->nullable()->after('price_retail');
            $table->decimal('price_insurance', 12, 2)->nullable()->after('price_wholesale');
            $table->string('promo_name')->nullable()->after('description');
            $table->decimal('promo_percent', 5, 2)->default(0)->after('promo_name');
            $table->integer('bundle_qty')->nullable()->after('promo_percent');
            $table->decimal('bundle_price', 12, 2)->nullable()->after('bundle_qty');
        });

        // Backfill retail price from legacy price column
        DB::table('products')->update([
            'price_retail' => DB::raw('price')
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'price_retail',
                'price_wholesale',
                'price_insurance',
                'promo_name',
                'promo_percent',
                'bundle_qty',
                'bundle_price',
            ]);
        });
    }
}
