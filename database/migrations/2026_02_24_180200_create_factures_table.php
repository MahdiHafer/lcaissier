<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacturesTable extends Migration
{
    public function up()
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->date('date_facture');
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('bon_livraison_id')->nullable()->constrained('bons_livraison')->nullOnDelete();
            $table->foreignId('vente_id')->nullable()->constrained('ventes')->nullOnDelete();
            $table->decimal('total_ht', 12, 2)->default(0);
            $table->decimal('tva_rate', 5, 2)->default(0);
            $table->decimal('tva_amount', 12, 2)->default(0);
            $table->decimal('total_ttc', 12, 2)->default(0);
            $table->string('legal_company_name')->nullable();
            $table->string('legal_ice')->nullable();
            $table->string('legal_rc')->nullable();
            $table->string('legal_if')->nullable();
            $table->string('legal_cnss')->nullable();
            $table->string('legal_address')->nullable();
            $table->string('legal_phone')->nullable();
            $table->string('legal_email')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('factures');
    }
}

