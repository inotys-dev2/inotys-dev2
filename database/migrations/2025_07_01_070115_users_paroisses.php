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
        Schema::create('users_paroisses', function (Blueprint $table) {
            $table->bigIncrements('id')->autoIncrement();
            $table->foreignId('users_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('paroisses_id')->constrained('paroisses')->onDelete('cascade');
            $table->string('rank', 50)->enum('officiant', 'benevole');
            $table->unique(['users_id', 'paroisses_id']);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("users_paroisses");

    }
};
