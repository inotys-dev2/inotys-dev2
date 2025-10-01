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
        Schema::create('demande_ceremonies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained('entreprise');
            $table->foreignId('paroisses_id')->constrained('paroisses');
            $table->foreignId('users_paroisses_id')->nullable()->constrained('users_paroisses');
            $table->string('nom_defunt');
            $table->dateTime('date_ceremonie');
            $table->time('heure_ceremonie');
            $table->integer('duree_minutes')->default(60);
            $table->string('nom_contact_famille')->nullable();
            $table->string('telephone_contact_famille', 20)->nullable();
            $table->text('demandes_speciales')->nullable();
            $table->enum('statut', ['en_attente', 'acceptee', 'refusee', 'passee' ])->default('en_attente');
            $table->decimal('montant', 8, 2)->nullable();
            $table->enum('statut_paiement', ['en_attente', 'paye', 'annule'])->default('en_attente');
            $table->foreignId('cree_par')->constrained('users');;
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("demandes_ceremonies");
    }
};
