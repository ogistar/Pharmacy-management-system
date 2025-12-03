<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ConvertPurchasesQuantityToInteger extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration will add a new integer column `quantity_int`, copy
     * numeric values from the existing `quantity` (string) column,
     * then drop the old column and rename the new one to `quantity`.
     * This is safer than altering type in-place and avoids DBAL requirement.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('purchases')) {
            return;
        }

        Schema::table('purchases', function (Blueprint $table) {
            // new integer column with default 0
            $table->integer('quantity_int')->default(0)->after('cost_price');
        });

        // Copy values from old quantity (string) to new integer column
        $purchases = DB::table('purchases')->select('id', 'quantity')->get();
        foreach ($purchases as $p) {
            $val = intval(preg_replace('/[^0-9]/', '', (string)$p->quantity));
            DB::table('purchases')->where('id', $p->id)->update(['quantity_int' => $val]);
        }

        // Now replace the old string column with the integer column in a driver-safe way.
        // We avoid relying on the Schema change() which needs DBAL on some drivers.
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            // For MySQL we can DROP the old column then CHANGE the new one into the final name.
            DB::statement("ALTER TABLE `purchases` DROP COLUMN `quantity`, CHANGE `quantity_int` `quantity` INT NOT NULL DEFAULT 0");
        } elseif ($driver === 'pgsql') {
            // Postgres approach: drop old (if present), rename new, ensure type
            DB::statement('ALTER TABLE purchases DROP COLUMN IF EXISTS quantity');
            DB::statement('ALTER TABLE purchases RENAME COLUMN quantity_int TO quantity');
            DB::statement('ALTER TABLE purchases ALTER COLUMN quantity TYPE integer USING quantity::integer');
            DB::statement("ALTER TABLE purchases ALTER COLUMN quantity SET DEFAULT 0");
        } else {
            // Fallback: attempt to rename via Schema (may require DBAL). If that fails, leave quantity_int as-is.
            try {
                Schema::table('purchases', function (Blueprint $table) {
                    $table->renameColumn('quantity_int', 'quantity');
                });
            } catch (\Exception $e) {
                // as a last resort, keep quantity_int present (manual migration required on some drivers)
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('purchases')) {
            return;
        }

        // Best-effort rollback: create a string column, copy integer values back, and drop integer column.
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `purchases` ADD COLUMN `quantity_str` VARCHAR(255) DEFAULT '0'");
            $purchases = DB::table('purchases')->select('id', 'quantity')->get();
            foreach ($purchases as $p) {
                DB::table('purchases')->where('id', $p->id)->update(['quantity_str' => (string)$p->quantity]);
            }
            DB::statement('ALTER TABLE `purchases` DROP COLUMN `quantity`');
            DB::statement("ALTER TABLE `purchases` CHANGE `quantity_str` `quantity` VARCHAR(255) DEFAULT '0'");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE purchases ADD COLUMN quantity_str VARCHAR(255) DEFAULT '0'");
            $purchases = DB::table('purchases')->select('id', 'quantity')->get();
            foreach ($purchases as $p) {
                DB::table('purchases')->where('id', $p->id)->update(['quantity_str' => (string)$p->quantity]);
            }
            DB::statement('ALTER TABLE purchases DROP COLUMN IF EXISTS quantity');
            DB::statement("ALTER TABLE purchases RENAME COLUMN quantity_str TO quantity");
        } else {
            // Fallback simple approach using Schema builder
            if (!Schema::hasColumn('purchases', 'quantity')) {
                Schema::table('purchases', function (Blueprint $table) {
                    $table->string('quantity')->default('0')->after('cost_price');
                });
            }
            $purchases = DB::table('purchases')->select('id', 'quantity')->get();
            foreach ($purchases as $p) {
                DB::table('purchases')->where('id', $p->id)->update(['quantity' => (string)$p->quantity]);
            }
            // cannot reliably drop integer column in fallback
        }
    }
}
