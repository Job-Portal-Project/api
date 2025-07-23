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
        Schema::create('jwt_token_blacklist', function (Blueprint $table) {
		$table->id();
            	$table->uuid('jwt_token_id');
            	$table->timestamps();

            	$table->foreign('jwt_token_id')->references('id')->on('jwt_tokens');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jwt_token_blacklist');
    }
};
