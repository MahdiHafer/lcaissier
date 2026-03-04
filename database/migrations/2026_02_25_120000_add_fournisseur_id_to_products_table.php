<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddFournisseurIdToProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'fournisseur_id')) {
                $table->unsignedBigInteger('fournisseur_id')->nullable()->after('codebar');
            }
        });

        if (Schema::hasColumn('products', 'imei')) {
            DB::table('products')
                ->whereNull('fournisseur_id')
                ->update(['fournisseur_id' => DB::raw('imei')]);
        }
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'fournisseur_id')) {
                $table->dropColumn('fournisseur_id');
            }
        });
    }
}