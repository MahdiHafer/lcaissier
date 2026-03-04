<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvoirDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('avoir_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('avoir_id')->constrained('avoirs')->cascadeOnDelete();
            $table->foreignId('vente_detail_id')->nullable()->constrained('ventes_details')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('reference_produit')->nullable();
            $table->string('nom_produit');
            $table->integer('quantite')->default(1);
            $table->decimal('prix_unitaire', 12, 2)->default(0);
            $table->decimal('total_ligne', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('avoir_details');
    }
}