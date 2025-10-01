@extends('entreprise.layouts.app')

@section('content')
    <div class="container">
        <h1 class="page-title">
            {{ $demande ? 'Modifier la demande' : 'Nouvelle demande de cérémonie' }}
        </h1>

        @if(session('success'))
            <p class="alert alert-success">{{ session('success') }}</p>
        @endif

        <form method="POST" action="{{ route('entreprise.agenda.envoyer', $entreprise->uuid) }}?id={{ $demande->id ?? '' }}"
              class="form form-demande">
            @csrf

            {{-- Paroisse --}}
            <div class="form-group">
                <label for="paroisses_id" class="form-label">Paroisse</label>
                <select name="paroisses_id" id="paroisses_id" class="form-select">
                    <option value="">Veuillez choisir la paroisse...</option>
                    @foreach($paroisses as $p)
                        <option value="{{ $p->id }}"
                            {{ old('paroisses_id', optional($demande)->paroisses_id) == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
                @error('paroisses_id')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nom du défunt --}}
            <div class="form-group">
                <label for="nom_defunt" class="form-label">Nom du défunt</label>
                <input type="text"
                       name="nom_defunt"
                       id="nom_defunt"
                       class="form-input"
                       placeholder="Alex Dupont"
                       value="{{ old('nom_defunt', optional($demande)->nom_defunt) }}">
                @error('nom_defunt')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Date & Heure --}}
            <div class="form-row">
                <div class="form-group form-group-half">
                    <label for="date_ceremonie" class="form-label">Date</label>
                    <input type="date"
                           name="date_ceremonie"
                           id="date_ceremonie"
                           class="form-input"
                           value="{{ old('date_ceremonie', $defaultDate) }}">
                    @error('date_ceremonie')
                    <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group form-group-half">
                    <label for="heure_ceremonie" class="form-label">Heure</label>
                    <input type="time"
                           name="heure_ceremonie"
                           id="heure_ceremonie"
                           class="form-input"
                           value="{{ old('heure_ceremonie', $defaultTime) }}">
                    @error('heure_ceremonie')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Durée --}}
            <div class="form-group">
                <label for="duree_minutes" class="form-label">Durée (minutes)</label>
                <input type="number"
                       name="duree_minutes"
                       id="duree_minutes"
                       class="form-input"
                       placeholder="60 = 1h"
                       value="{{ old('duree_minutes', $defaultDuration) }}">
                @error('duree_minutes')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Contact famille --}}
            <div class="form-group">
                <label for="nom_contact_famille" class="form-label">Nom du contact (famille)</label>
                <input type="text"
                       name="nom_contact_famille"
                       id="nom_contact_famille"
                       class="form-input"
                       placeholder="Pierre Dupont"
                       value="{{ old('nom_contact_famille', optional($demande)->nom_contact_famille) }}">
                @error('nom_contact_famille')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group">
                <label for="telephone_contact_famille" class="form-label">Téléphone du contact (famille)</label>
                <input type="text"
                       name="telephone_contact_famille"
                       id="telephone_contact_famille"
                       class="form-input"
                       placeholder="06XXXXXXXX"
                       value="{{ old('telephone_contact_famille', optional($demande)->telephone_contact_famille) }}">
                @error('telephone_contact_famille')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Demandes spéciales --}}
            <div class="form-group">
                <label for="demandes_speciales" class="form-label">Demandes spéciales</label>
                <textarea name="demandes_speciales"
                          id="demandes_speciales"
                          class="form-textarea">{{ old('demandes_speciales', optional($demande)->demandes_speciales) }}</textarea>
                @error('demandes_speciales')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn btn-submit">
                {{ $demande ? 'Mettre à jour' : 'Envoyer la demande' }}
            </button>
        </form>
    </div>
@endsection
