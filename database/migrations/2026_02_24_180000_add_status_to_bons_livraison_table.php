<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToBonsLivraisonTable extends Migration
{
    public function up()
    {
        Schema::table('bons_livraison', function (Blueprint $table) {
            $table->string('status')->default('brouillon')->after('total');
        });
    }

    public function down()
    {
        Schema::table('bons_livraison', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}

