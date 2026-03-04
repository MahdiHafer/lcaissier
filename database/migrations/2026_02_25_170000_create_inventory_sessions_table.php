<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventorySessionsTable extends Migration
{
    public function up()
    {
        Schema::create('inventory_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->date('date_inventaire');
            $table->string('status')->default('brouillon');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_sessions');
    }
}