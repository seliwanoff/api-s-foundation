<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBvnNinEducationToUsersTable extends Migration
{


    public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('bvn')->nullable();
        $table->string('nin')->nullable();
        $table->enum('level_of_education', ['Uneducated', 'Primary School', 'Secondary School', 'OND', 'HND', 'BSc', 'MSc', 'PhD'])->nullable();
        $table->boolean('is_disabled')->default(false); // for disability status
        $table->text('comment')->nullable();
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['bvn', 'nin', 'level_of_education', 'is_disabled', 'comment']);
    });
}

}