<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVenteIdToBonLivraisonDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bon_livraison_details', function (Blueprint $table) {
            $table->foreignId('vente_id')
                ->nullable()
                ->after('bon_livraison_id')
                ->constrained('ventes')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bon_livraison_details', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vente_id');
        });
    }
}

