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
        Schema::create('reversements', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('facture_paroissial_id')->constrained('factures_paroisse')->onDelete('cascade');
            $table->foreignId('entreprise_id')->constrained('entreprise')->onDelete('cascade');
            $table->decimal('montant', 10, 2);
            $table->enum('status', ['en_attente', 'recu', 'echoue', 'partiel'])->default('en_attente');
            $table->string('reference')->nullable(); // référence de virement
            $table->text('preuve')->nullable(); // lien ou fichier (selon stockage)
            $table->timestamp('date_initiation')->useCurrent();
            $table->timestamp('date_reception')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("reversements");

    }
};
