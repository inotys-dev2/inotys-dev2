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
            $table->foreignId('user_entreprise_id')->constrained('users');;
            $table->foreignId('paroisse_id')->constrained('paroisse');
            $table->foreignId('users_paroisses_id')->nullable()->constrained('users_paroisses');
            $table->foreignId('assigned_at')->nullable()->constrained('users');
            $table->string('deceased_name');
            $table->date('ceremony_date');
            $table->time('ceremony_hour');
            $table->integer('duration_time')->default(60);
            $table->string('contact_family_name')->nullable();
            $table->string('telephone_contact_family', 20)->nullable();
            $table->text('special_requests')->nullable();
            $table->enum('statut', ['treatment','waiting', 'accepted', 'canceled', 'passed' ])->default('treatment');
            $table->text('cancel_reason')->nullable();
            $table->decimal('sum', 8, 2)->nullable();
            $table->enum('statut_paiement', ['define','waiting', 'paid', 'canceled'])->default('define');
            $table->integer("score")->default(0);
            $table->timestamps();
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
