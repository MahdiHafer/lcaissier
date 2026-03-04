<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvoirsTable extends Migration
{
    public function up()
    {
        Schema::create('avoirs', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->date('date_avoir');
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('vente_id')->constrained('ventes')->cascadeOnDelete();
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('avoirs');
    }
}