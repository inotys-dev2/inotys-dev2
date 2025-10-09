@extends('entreprise.layouts.app')

@section('content')
    <div class="container">

        {{-- Titre --}}
        <h1 class="page-title">
            {{ isset($ceremony) ? 'Modifier la demande de cérémonie' : 'Nouvelle demande de cérémonie' }}
        </h1>

        {{-- Message de succès --}}
        @if(session('success'))
            <p class="alert alert-success">{{ session('success') }}</p>
        @endif

        {{-- Formulaire --}}
        <form
            method="POST"
            action="{{ route('entreprise.agenda.envoyer', $entreprise->uuid) }}{{ isset($ceremony) ? '?id='.$ceremony->id : '' }}"
            class="form form-demande"
        >
            @csrf

            {{-- ===================== PAROISSE ===================== --}}
            <div class="form-group">
                <label for="paroisse_id" class="form-label">Paroisse <span class="text-danger">*</span></label>
                <select
                    name="paroisse_id"
                    id="paroisse_id"
                    class="form-select"
                    required
                    title="Veuillez sélectionner une paroisse"
                >
                    <option value="">Sélectionnez une paroisse...</option>
                    @foreach($parishes as $parish)
                        <option
                            value="{{ $parish->id }}"
                            {{ old('paroisse_id', optional($ceremony)->paroisse_id) == $parish->id ? 'selected' : '' }}
                        >
                            {{ $parish->name }}
                        </option>
                    @endforeach
                </select>
                @error('paroisse_id')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- ===================== NOM DU DÉFUNT ===================== --}}
            <div class="form-group">
                <label for="deceased_name" class="form-label">Nom du défunt <span class="text-danger">*</span></label>
                <input
                    type="text"
                    name="deceased_name"
                    id="deceased_name"
                    class="form-input"
                    placeholder="Ex : Jean Dupont"
                    value="{{ old('deceased_name', optional($ceremony)->deceased_name) }}"
                    required
                    pattern="^[A-Za-zÀ-ÖØ-öø-ÿ' \-]{2,60}$"
                    title="Entrez entre 2 et 60 caractères : lettres, espaces, apostrophes ou tirets (ex : Jean-Pierre, O’Connor)"
                >
                @error('deceased_name')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- ===================== DATE & HEURE ===================== --}}
            <div class="form-row">
                <div class="form-group form-group-half">
                    <label for="ceremony_date" class="form-label">Date de la cérémonie <span class="text-danger">*</span></label>
                    <input
                        type="date"
                        name="ceremony_date"
                        id="ceremony_date"
                        class="form-input"
                        required
                        value="{{ old('ceremony_date', $defaultDate) }}"
                        title="Sélectionnez la date de la cérémonie"
                    >
                    @error('ceremony_date')
                    <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group form-group-half">
                    <label for="ceremony_hour" class="form-label">Heure de la cérémonie <span class="text-danger">*</span></label>
                    <input
                        type="time"
                        name="ceremony_hour"
                        id="ceremony_hour"
                        class="form-input"
                        required
                        value="{{ old('ceremony_hour', $defaultTime) }}"
                        title="Sélectionnez l'heure de la cérémonie"
                    >
                    @error('ceremony_hour')
                    <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- ===================== DURÉE ===================== --}}
            <div class="form-group">
                <label for="duration_time" class="form-label">Durée (en minutes)</label>
                <input
                    type="number"
                    name="duration_time"
                    id="duration_time"
                    class="form-input"
                    placeholder="Ex : 60 = 1h"
                    value="{{ old('duration_time', $defaultDuration) }}"
                    min="15"
                    title="Entrez la durée estimée de la cérémonie en minutes"
                >
                @error('duration_time')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- ===================== CONTACT FAMILLE ===================== --}}
            <div class="form-group">
                <label for="contact_family_name" class="form-label">Nom du contact famille</label>
                <input
                    type="text"
                    name="contact_family_name"
                    id="contact_family_name"
                    class="form-input"
                    placeholder="Ex : Pierre Dupont"
                    value="{{ old('contact_family_name', optional($ceremony)->contact_family_name) }}"
                    pattern="^[A-Za-zÀ-ÖØ-öø-ÿ' \-]{2,60}$"
                    title="Entrez entre 2 et 60 caractères : lettres, espaces, apostrophes ou tirets"
                >
                @error('contact_family_name')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="telephone_contact_family" class="form-label">Téléphone du contact</label>
                <input
                    type="tel"
                    name="telephone_contact_family"
                    id="telephone_contact_family"
                    class="form-input"
                    placeholder="+33 6 12 34 56 78"
                    value="{{ old('telephone_contact_family', optional($ceremony)->telephone_contact_family) }}"
                    pattern="^\+?[0-9\s\-]{6,20}$"
                    title="Entrez un numéro valide (ex : +33 6 12 34 56 78)"
                >
                @error('telephone_contact_family')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- ===================== DEMANDES SPÉCIALES ===================== --}}
            <div class="form-group">
                <label for="special_requests" class="form-label">Demandes spéciales</label>
                <textarea
                    name="special_requests"
                    id="special_requests"
                    class="form-textarea"
                    placeholder="Souhaits particuliers pour la cérémonie..."
                >{{ old('special_requests', optional($ceremony)->special_requests) }}</textarea>
                @error('special_requests')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- ===================== BOUTON ===================== --}}
            <button type="submit" class="btn btn-primary">
                {{ isset($ceremony) ? 'Mettre à jour la demande' : 'Envoyer la demande' }}
            </button>
        </form>
    </div>
@endsection
