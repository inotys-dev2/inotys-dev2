<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('prenom', 100);
            $table->string('nom', 100);
            $table->string('profileImg')->default('default_avatar.png');
            $table->string('telephone', 20)->nullable();
            $table->enum('access', ['admin', 'entreprise', 'paroisses']);
            $table->string('role')->default('employer');
            $table->string('theme')->default('default');
            $table->timestamp('last_seen')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
