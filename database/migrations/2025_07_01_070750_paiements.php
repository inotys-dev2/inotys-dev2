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
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_ceremonie_id')->constrained('demande_ceremonies')->onDelete('cascade');
            $table->foreignId('entreprise_id')->constrained('entreprise')->onDelete('cascade'); // remplace network_id si c'est ça
            $table->decimal('montant_total', 10, 2);
            $table->string('methode_paiement', 50)->nullable();
            $table->string('provider_payment_id')->nullable(); // ex : stripe charge / intent
            $table->json('metadata')->nullable(); // ventilation : { "paroisse": 200.00, "pompe": 800.00 }
            $table->enum('statut', ['en_attente', 'paye', 'echec', 'rembourse'])->default('en_attente');
            $table->timestamp('paye_le')->nullable();
            $table->string('reference_paiement')->nullable(); // ref interne si besoin
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("paiements");

    }
};
