<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Foreign key for users
            $table->decimal('amount_due', 10, 2); // Total amount due
            $table->decimal('amount_paid', 10, 2)->default(0); // Amount paid so far
            $table->enum('payment_status', ['Pending', 'Paid'])->default('Pending'); // Payment status
            $table->timestamps(); // created_at and updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
}