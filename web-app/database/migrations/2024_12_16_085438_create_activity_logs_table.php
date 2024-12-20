<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id('log_id'); // Auto-increment primary key
            $table->unsignedBigInteger('user_id')->nullable(); // Foreign key reference to users table, use "set null" for delete
            $table->string('action'); // Action description
            $table->timestamp('action_time')->useCurrent(); // Action timestamp (default to current time)
            $table->string('ip_address', 45)->nullable(); // IP address
            $table->text('user_agent')->nullable(); // User agent
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_log');
    }
}