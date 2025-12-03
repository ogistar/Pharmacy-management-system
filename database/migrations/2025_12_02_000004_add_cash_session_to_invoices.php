<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCashSessionToInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'cash_session_id')) {
                $table->foreignId('cash_session_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('cash_sessions')
                    ->onDelete('set null');
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
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'cash_session_id')) {
                $table->dropForeign(['cash_session_id']);
                $table->dropColumn('cash_session_id');
            }
        });
    }
}
