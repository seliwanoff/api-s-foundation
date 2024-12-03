<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('surname');
            $table->string('firstname');
            $table->string('othername')->nullable();
            $table->enum('sex', ['male', 'female']);
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed']);
            $table->string('phoneNumber')->unique();
            $table->string('localgovernment');
            $table->text('address');
            $table->string('occupation');
            $table->string('shop_address');
            $table->string('purpose');
            $table->decimal('amount', 8, 2);  // Assuming amount is a decimal with 2 decimal places
            $table->timestamps();  // Automatically adds created_at and updated_at columns
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}