<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBankDetailsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('bank_account_number')->nullable();
        $table->string('account_name')->nullable();
        $table->string('bank_name')->nullable();
        $table->string('image')->nullable();  // Image field (store the file path)
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['bank_account_number', 'account_name', 'bank_name', 'image']);
    });
}

}