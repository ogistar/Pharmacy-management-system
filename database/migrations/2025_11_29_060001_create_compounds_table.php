<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompoundsTable extends Migration
{
    public function up()
    {
        Schema::create('compounds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('service_fee', 12, 2)->default(0);
            $table->decimal('markup_percent', 5, 2)->default(0);
            $table->decimal('price_override', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('compounds');
    }
}
