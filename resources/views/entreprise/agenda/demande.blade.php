{{--
    On étend le layout principal du tableau de bord entreprise
    pour hériter du header, sidebar et footer.
--}}
@extends('entreprise.layouts.app')

{{-- Section principale du contenu de la page --}}
@section('content')
    <div class="container">

        {{-- Titre de la page : affichage conditionnel selon si on modifie ou crée une demande --}}
        <h1 class="page-title">
            {{ $demande ? 'Modifier la demande' : 'Nouvelle demande de cérémonie' }}
        </h1>

        {{-- Message de succès après envoi ou mise à jour du formulaire --}}
        @if(session('success'))
            <p class="alert alert-success">{{ session('success') }}</p>
        @endif

        {{--
            Formulaire d’envoi de la demande de cérémonie
            - Méthode POST
            - Action : route entreprise.agenda.envoyer avec l’UUID de l’entreprise
            - Si on modifie une demande, on ajoute son ID dans l’URL (?id=)
        --}}
        <form
            method="POST"
            action="{{ route('entreprise.agenda.envoyer', $entreprise->uuid) }}?id={{ $demande->id ?? '' }}"
            class="form form-demande"
        >
            {{-- Protection CSRF --}}
            @csrf

            {{-- ===================== CHAMP PAROISSE ===================== --}}
            <div class="form-group">
                <label for="paroisses_id" class="form-label">Paroisse <span style="color: red">*</span></label>

                <select
                    name="paroisses_id"
                    id="paroisses_id"
                    class="form-select"
                    required
                    title="Veuillez choisir une paroisse"
                >
                    <option value="">Veuillez choisir la paroisse...</option>
                    @foreach($paroisses as $p)
                        <option
                            value="{{ $p->id }}"
                            {{ old('paroisses_id', optional($demande)->paroisses_id) == $p->id ? 'selected' : '' }}
                        >
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>

                @error('paroisses_id')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>


            {{-- ===================== CHAMP NOM DU DÉFUNT ===================== --}}
            <div class="form-group">
                <label for="nom_defunt" class="form-label">Nom du défunt <span style="color: red">*</span></label>
                <input
                    type="text"
                    name="nom_defunt"
                    id="nom_defunt"
                    class="form-input"
                    placeholder="Alex Dupont"
                    value="{{ old('nom_defunt', optional($demande)->nom_defunt) }}"
                    required
                    pattern="^[A-Za-zÀ-ÖØ-öø-ÿ' -]{2,60}$"
                    title="Entrez 2 à 60 caractères : lettres, espaces, apostrophes ou tirets (ex : Jean-Pierre, O’Connor)"
                >
                @error('nom_defunt')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- ===================== DATE ET HEURE ===================== --}}
            <div class="form-row">
                {{-- Date de la cérémonie --}}
                <div class="form-group form-group-half">
                    <label for="date_ceremonie" class="form-label">Date</label>
                    <input
                        type="date"
                        name="date_ceremonie"
                        id="date_ceremonie"
                        class="form-input"
                        value="{{ old('date_ceremonie', $defaultDate) }}"
                    >
                    @error('date_ceremonie')
                    <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Heure de la cérémonie --}}
                <div class="form-group form-group-half">
                    <label for="heure_ceremonie" class="form-label">Heure</label>
                    <input
                        type="time"
                        name="heure_ceremonie"
                        id="heure_ceremonie"
                        class="form-input"
                        value="{{ old('heure_ceremonie', $defaultTime) }}"
                    >
                    @error('heure_ceremonie')
                    <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- ===================== DURÉE ===================== --}}
            <div class="form-group">
                <label for="duree_minutes" class="form-label">Durée (minutes)</label>
                <input
                    type="number"
                    name="duree_minutes"
                    id="duree_minutes"
                    class="form-input"
                    placeholder="60 = 1h"
                    value="{{ old('duree_minutes', $defaultDuration) }}"
                >
                @error('duree_minutes')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- ===================== CONTACT FAMILLE ===================== --}}
            <div class="form-group">
                <label for="nom_contact_famille" class="form-label">Nom du contact (famille)</label>
                <input
                    type="text"
                    name="nom_contact_famille"
                    id="nom_contact_famille"
                    class="form-input"
                    placeholder="Pierre Dupont"
                    value="{{ old('nom_contact_famille', optional($demande)->nom_contact_famille) }}"
                    {{-- autorise lettres (avec accents), espaces, apostrophes et tirets, 2 à 60 caractères --}}
                    pattern="^[A-Za-zÀ-ÖØ-öø-ÿ' -]{2,60}$"
                    title="Entrez 2 à 60 caractères : lettres, espaces, apostrophes ou tirets (ex : Jean-Pierre, O’Connor)"
                >
                @error('nom_contact_famille')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="telephone_contact_famille" class="form-label">Téléphone du contact (famille)</label>
                <input
                    type="tel"
                    name="telephone_contact_famille"
                    id="telephone_contact_famille"
                    class="form-input"
                    placeholder="+33 6 12 34 56 78"
                    value="{{ old('telephone_contact_famille', optional($demande)->telephone_contact_famille) }}"
                    {{-- international : + optionnel, chiffres/espaces/tirets, 6 à 20 caractères --}}
                    pattern="^\+?[0-9\s\-]{6,20}$"
                    title="Entrez un numéro valide, ex : +33 6 12 34 56 78"
                />
                @error('telephone_contact_famille')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- ===================== DEMANDES SPÉCIALES ===================== --}}
            <div class="form-group">
                <label for="demandes_speciales" class="form-label">Demandes spéciales</label>
                <textarea
                    name="demandes_speciales"
                    id="demandes_speciales"
                    class="form-textarea"
                >{{ old('demandes_speciales', optional($demande)->demandes_speciales) }}</textarea>
                @error('demandes_speciales')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- ===================== BOUTON DE VALIDATION ===================== --}}
            <button type="submit" class="btn btn-submit">
                {{ $demande ? 'Mettre à jour' : 'Envoyer la demande' }}
            </button>
        </form>
    </div>
@endsection
