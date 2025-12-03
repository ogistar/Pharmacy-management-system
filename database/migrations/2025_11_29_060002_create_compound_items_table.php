<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompoundItemsTable extends Migration
{
    public function up()
    {
        Schema::create('compound_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compound_id')->constrained('compounds')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity')->default(1); // per 1 unit racikan
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('compound_items');
    }
}
