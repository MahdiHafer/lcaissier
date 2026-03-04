<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuantiteRetourneeToVentesDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('ventes_details', function (Blueprint $table) {
            if (!Schema::hasColumn('ventes_details', 'quantite_retournee')) {
                $table->integer('quantite_retournee')->default(0)->after('quantite');
            }
        });
    }

    public function down()
    {
        Schema::table('ventes_details', function (Blueprint $table) {
            if (Schema::hasColumn('ventes_details', 'quantite_retournee')) {
                $table->dropColumn('quantite_retournee');
            }
        });
    }
}