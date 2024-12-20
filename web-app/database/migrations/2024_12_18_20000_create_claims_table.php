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
        Schema::create('claims', function (Blueprint $table) {
            $table->id('claim_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('claim_status', ['Pending', 'Approved', 'Rejected']);
            $table->text('proof_of_ownership')->nullable();
            $table->date('submission_date');
            $table->foreign('item_id')->references('id')->on('found_items')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
