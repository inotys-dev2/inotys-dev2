<div class="demande-card__row">
    <span class="demande-card__label">Officiant :</span>
    <span class="demande-card__value">
        @if($demande->officiant && $demande->officiant->user)
            {{ $demande->officiant->user->prenom }} {{ $demande->officiant->user->nom }}
        @else
            <em>Aucun officiant</em>
        @endif
    </span>
</div>

<div class="demande-card__row">
    <span class="demande-card__label">Date :</span>
    <span class="demande-card__value">
        {{ \Carbon\Carbon::parse($demande->date_ceremonie)->format('d/m/Y') }}
        – {{ \Carbon\Carbon::parse($demande->heure_ceremonie)->format('H:i') }}
        ({{ $demande->duree_minutes }} min)
    </span>
</div>

<div class="demande-card__row">
    <span class="demande-card__label">Défunt :</span>
    <span class="demande-card__value">
        {{ $demande->nom_defunt }}
    </span>
</div>

<div class="demande-card__row">
    <span class="demande-card__label">Contact :</span>
    <span class="demande-card__value">
        {{ $demande->nom_contact_famille }}
    </span>
</div>

<div class="demande-card__row">
    <span class="demande-card__label">Téléphone :</span>
    <span class="demande-card__value">{{ $demande->telephone_contact_famille }}
    </span>
</div>
