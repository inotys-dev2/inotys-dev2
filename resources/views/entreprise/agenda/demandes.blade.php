{{--
    Fichier : resources/views/entreprise/agenda/demandes.blade.php
    Description : Page d’affichage des demandes de cérémonies pour une entreprise
    Les demandes sont classées en 3 sections :
    - Cérémonies confirmées
    - Demandes en attente
    - Demandes passées ou annulées
--}}

@extends('entreprise.layouts.app')

@section('content')
    <div class="demandes-container">

        {{-- ========================== SECTION : CÉRÉMONIES CONFIRMÉES ========================== --}}
        <section class="demandes-section demandes-section--confirmees">
            <h2 class="demandes-title">Cérémonies confirmées</h2>

            {{-- Si aucune demande confirmée --}}
            @if($demandesConfirmees->isEmpty())
                <p class="demandes-empty">
                    <em>Aucune cérémonie confirmée.</em>
                </p>
            @else
                {{-- Liste des cérémonies confirmées --}}
                <ul class="demandes-list">
                    {{-- Tri par date de cérémonie pour un affichage chronologique --}}
                    @foreach($demandesConfirmees->sortBy('date_ceremonie') as $demande)
                        <li
                            class="demande-item acceptee clickable"
                            data-id="{{ $demande->id }}"
                        >
                            {{-- Inclusion du composant d’affichage des infos de la demande --}}
                            @include('entreprise.agenda.components.demande-info', ['demande' => $demande])
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>


        {{-- Séparateur visuel entre les sections --}}
        <hr class="demandes-divider">


        {{-- ========================== SECTION : DEMANDES EN ATTENTE ========================== --}}
        <section class="demandes-section demandes-section--en-attente">
            <h2 class="demandes-title">Demandes en attente</h2>

            {{-- Si aucune demande en attente --}}
            @if($demandesEnAttente->isEmpty())
                <p class="demandes-empty">
                    <em>Aucune demande en attente.</em>
                </p>
            @else
                {{-- Liste des demandes en attente --}}
                <ul class="demandes-list">
                    @foreach($demandesEnAttente->sortBy('date_ceremonie') as $demande)
                        <li
                            class="demande-item en-attente clickable"
                            data-id="{{ $demande->id }}"
                        >
                            {{-- Inclusion du composant d’affichage --}}
                            @include('entreprise.agenda.components.demande-info', ['demande' => $demande])
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <hr class="demandes-divider">

        {{-- ========================== SECTION : PASSÉES OU ANNULÉES ========================== --}}
        <section class="demandes-section demandes-section--passee-ou-annulee">
            <h2 class="demandes-title">Demandes passées ou annulées</h2>

            {{-- Si aucune demande passée/annulée --}}
            @if($demandesPasseeOuAnnulee->isEmpty())
                <p class="demandes-empty">
                    <em>Aucune demande passée ou annulée.</em>
                </p>
            @else
                {{-- Liste des demandes passées ou annulées --}}
                <ul class="demandes-list">
                    @foreach($demandesPasseeOuAnnulee->sortBy('date_ceremonie') as $demande)
                        {{--
                            La classe CSS est dynamique selon le statut :
                            par exemple 'annulee', 'passee', etc.
                        --}}
                        <li class="demande-item {{ $demande->statut }}">
                            @include('entreprise.agenda.components.demande-info', ['demande' => $demande])
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
