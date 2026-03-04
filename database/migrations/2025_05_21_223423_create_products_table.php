<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('reference')->unique();
        $table->string('categorie');
        $table->string('etat');
        $table->string('marque');
        $table->string('modele');
        $table->string('ram')->nullable();
        $table->string('stockage')->nullable();
        $table->string('processeur')->nullable();
        $table->double('prix_achat');
        $table->double('prix_vente');
        $table->integer('quantite');
        $table->string('codebar')->nullable();
        $table->string('imei')->nullable();
        $table->string('image')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
