<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVentesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
Schema::create('ventes', function (Blueprint $table) {
    $table->id();
    $table->string('numero_ticket')->unique();
    $table->decimal('total', 10, 2);
    $table->decimal('remise', 10, 2)->default(0);
    $table->decimal('net_a_payer', 10, 2);
    $table->decimal('montant_paye', 10, 2);
    $table->decimal('rendu', 10, 2)->nullable();
    $table->enum('mode_paiement', ['espèces', 'TPE', 'chèque', 'crédit', 'bon_achat']);
    $table->string('client')->nullable();
    $table->unsignedBigInteger('user_id');
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
        Schema::dropIfExists('ventes');
    }
}
