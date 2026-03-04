<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuantiteVendueToBonLivraisonDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('bon_livraison_details', function (Blueprint $table) {
            $table->integer('quantite_vendue')->default(0)->after('quantite');
        });
    }

    public function down()
    {
        Schema::table('bon_livraison_details', function (Blueprint $table) {
            $table->dropColumn('quantite_vendue');
        });
    }
}

