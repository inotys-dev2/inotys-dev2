{{-- resources/views/entreprise/agenda/demandes.blade.php --}}
@extends('entreprise.layouts.app')

@section('content')

    <div class="demandes-container">

        {{-- Section : Confirmées --}}
        <section class="demandes-section demandes-section--confirmees">
            <h2 class="demandes-title">Cérémonies confirmées</h2>
            @if($demandesConfirmees->isEmpty())
                <p class="demandes-empty"><em>Aucune cérémonie confirmée.</em></p>
            @else
                <ul class="demandes-list">
                    @foreach($demandesConfirmees->sortBy('date_ceremonie') as $demande)
                        <li
                            class="demande-item acceptee clickable"
                            data-id="{{ $demande->id }}"
                        >
                            @include('entreprise.agenda.components.demande-info', ['demande' => $demande])
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>


        <hr class="demandes-divider">

        {{-- Section : En attente --}}
        <section class="demandes-section demandes-section--en-attente">
            <h2 class="demandes-title">Demandes en attente</h2>
            @if($demandesEnAttente->isEmpty())
                <p class="demandes-empty"><em>Aucune demande en attente.</em></p>
            @else
                <ul class="demandes-list">
                    @foreach($demandesEnAttente->sortBy('date_ceremonie') as $demande)
                        <li
                            class="demande-item en-attente clickable"
                            data-id="{{ $demande->id }}"
                        >
                            @include('entreprise.agenda.components.demande-info', ['demande' => $demande])
                        </li>
                    @endforeach

                </ul>
            @endif
        </section>

        <hr class="demandes-divider">

        {{-- Section : En attente --}}
        <section class="demandes-section demandes-section--passee-ou-annulee">
            <h2 class="demandes-title">Demandes passée ou annulée</h2>
            @if($demandesPasseeOuAnnulee->isEmpty())
                <p class="demandes-empty"><em>Aucune demande passée ou annulée.</em></p>
            @else
                <ul class="demandes-list">
                    @foreach($demandesPasseeOuAnnulee->sortBy('date_ceremonie') as $demande)
                        <li class="demande-item {{ $demande->statut  }}">
                            @include('entreprise.agenda.components.demande-info', ['demande' => $demande])
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
