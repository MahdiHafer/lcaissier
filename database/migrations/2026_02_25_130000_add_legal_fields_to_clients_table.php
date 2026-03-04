<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLegalFieldsToClientsTable extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('societe')->nullable()->after('nom');
            $table->string('ice')->nullable()->after('societe');
            $table->string('rc')->nullable()->after('ice');
            $table->string('if_fiscal')->nullable()->after('rc');
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['societe', 'ice', 'rc', 'if_fiscal']);
        });
    }
}