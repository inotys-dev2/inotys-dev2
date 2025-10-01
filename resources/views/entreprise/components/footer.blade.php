{{--
    Footer affiché en bas de toutes les pages du dashboard entreprise.
    Il contient les informations légales et la plage d'années dynamiques.
--}}
<footer class="footer">
    {{--
        &copy; = symbole ©
        Affiche le nom du site "Obsek.fr" suivi de l’année ou de la plage d’années.
        Si l’année actuelle est 2025, on affiche simplement "2025".
        Sinon, on affiche "2025 - année_actuelle".
        Cela permet d’éviter d’avoir à modifier le footer chaque année.
    --}}
    &copy; Obsek.fr -
    @if(date('Y') == 2025)
        2025
    @else
        2025 - {{ date('Y') }}
    @endif
</footer>
