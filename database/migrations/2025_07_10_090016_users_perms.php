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
        Schema::create('users_perms', function (Blueprint $table) {
            $table->id();

            // Si vous voulez le users.id
            $table->foreignId('users_id')
                ->constrained('users')          // ← users, pas users_entreprises
                ->onDelete('cascade');

            // Si vous voulez l’entreprise
            $table->foreignId('entreprise_id')
                ->nullable()
                ->constrained('entreprise')   // ← entreprises, pas users_entreprises
                ->onDelete('set null');

            // Permissions employé
            $table->boolean('permission_employe_creer_demande')->default(true);
            $table->boolean('permission_employe_voir_demande')->default(true);
            $table->boolean('permission_employe_modifier_demande')->default(true);

            // Permissions responsable
            $table->boolean('permission_responsable_valider_demande')->default(false);
            $table->boolean('permission_responsable_refuser_demande')->default(false);
            $table->boolean('permission_responsable_gerer_agenda')->default(false);
            $table->boolean('permission_responsable_generer_paiement')->default(false);
            $table->boolean('permission_responsable_suivre_paiement')->default(false);
            $table->boolean('permission_responsable_confirmer_officiant')->default(false);

            // Permissions administrateur
            $table->boolean('permission_administrateur_gerer_utilisateur')->default(false);
            $table->boolean('permission_administrateur_configurer_systeme')->default(false);
            $table->boolean('permission_administrateur_voir_historique')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_perms');
    }
};
