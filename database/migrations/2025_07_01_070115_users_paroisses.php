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
            $table->foreignId('paroisse_id')->constrained('paroisse')->onDelete('cascade');
            $table->unique(['users_id', 'paroisse_id']);
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
