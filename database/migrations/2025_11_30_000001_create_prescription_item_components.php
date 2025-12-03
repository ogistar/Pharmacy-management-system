<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrescriptionItemComponents extends Migration
{
    public function up()
    {
        Schema::create('prescription_item_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_item_id')->constrained('prescription_items')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->string('product_name')->nullable();
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('prescription_item_components');
    }
}
