<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->date('date');
            $table->string('category')->nullable();
            $table->string('description');
            $table->string('currency_key');
            $table->decimal('amount', 10, 2);
            $table->integer('num_payers');
            $table->string('payer_name');
            $table->string('receiver_name')->nullable();
            $table->boolean('is_settlement');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('group_id')->references('id')->on('groups');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
