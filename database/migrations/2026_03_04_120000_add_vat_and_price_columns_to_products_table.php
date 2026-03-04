<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddVatAndPriceColumnsToProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'tva_rate')) {
                $table->decimal('tva_rate', 5, 2)->default(0)->after('etat');
            }
            if (!Schema::hasColumn('products', 'prix_achat_ht')) {
                $table->decimal('prix_achat_ht', 12, 2)->default(0)->after('tva_rate');
            }
            if (!Schema::hasColumn('products', 'prix_achat_ttc')) {
                $table->decimal('prix_achat_ttc', 12, 2)->default(0)->after('prix_achat_ht');
            }
            if (!Schema::hasColumn('products', 'prix_vente_ht')) {
                $table->decimal('prix_vente_ht', 12, 2)->default(0)->after('prix_achat_ttc');
            }
            if (!Schema::hasColumn('products', 'prix_vente_ttc')) {
                $table->decimal('prix_vente_ttc', 12, 2)->default(0)->after('prix_vente_ht');
            }
        });

        DB::table('products')->update([
            'prix_achat_ht' => DB::raw('COALESCE(prix_achat, 0)'),
            'prix_achat_ttc' => DB::raw('COALESCE(prix_achat, 0)'),
            'prix_vente_ht' => DB::raw('COALESCE(prix_vente, 0)'),
            'prix_vente_ttc' => DB::raw('COALESCE(prix_vente, 0)'),
        ]);
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $drops = [];
            foreach (['tva_rate', 'prix_achat_ht', 'prix_achat_ttc', 'prix_vente_ht', 'prix_vente_ttc'] as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $drops[] = $col;
                }
            }
            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
}