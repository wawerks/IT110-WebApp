<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('found_items', function (Blueprint $table) {
            $table->id();
            $table->date('found_date');
            $table->string('item_name');
            $table->string('facebook_link');
            $table->string('contact_number');
            $table->text('description'); 
            $table->string('category'); 
            $table->string('location');  
            $table->text('image_url')->nullable(); 
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('found_items');
    }
};
