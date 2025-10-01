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
        Schema::create('factures_paroisse', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('paroisses_id')->constrained('paroisses')->onDelete('cascade');
            $table->foreignId('entreprise_id')->constrained('entreprise')->onDelete('cascade');
            $table->string('client_nom')->nullable();
            $table->text('description')->nullable();
            $table->decimal('montant_paroissial', 10, 2);
            $table->decimal('taxes', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('statut', ['emisd', 'envoye', 'en_attente_reversement', 'partiellement_reverse', 'regle', 'litige'])->default('emisd');
            $table->string('reference_externe')->nullable(); // pour la réconciliation
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("factures_paroisse");
    }
};
