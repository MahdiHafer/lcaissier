<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSourceAndDiscountToFacturesTable extends Migration
{
    public function up()
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->foreignId('devis_id')
                ->nullable()
                ->after('vente_id')
                ->constrained('devis')
                ->nullOnDelete();

            $table->string('remise_type', 5)->default('dh')->after('tva_rate');
            $table->decimal('remise_value', 12, 2)->default(0)->after('remise_type');
            $table->decimal('remise_amount', 12, 2)->default(0)->after('remise_value');
        });
    }

    public function down()
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropConstrainedForeignId('devis_id');
            $table->dropColumn(['remise_type', 'remise_value', 'remise_amount']);
        });
    }
}

