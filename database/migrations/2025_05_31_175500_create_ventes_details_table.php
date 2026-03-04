<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVentesDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ventes_details', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('vente_id');
    $table->string('reference_produit');
    $table->string('nom_produit');
    $table->integer('quantite');
    $table->decimal('prix_unitaire', 10, 2);
    $table->decimal('total_ligne', 10, 2);
    $table->timestamps();

    $table->foreign('vente_id')->references('id')->on('ventes')->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ventes_details');
    }
}
