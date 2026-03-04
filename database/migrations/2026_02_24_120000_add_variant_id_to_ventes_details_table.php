<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVariantIdToVentesDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ventes_details', function (Blueprint $table) {
            $table->foreignId('variant_id')
                ->nullable()
                ->after('reference_produit')
                ->constrained('product_variants')
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
        Schema::table('ventes_details', function (Blueprint $table) {
            $table->dropConstrainedForeignId('variant_id');
        });
    }
}

