<footer class="footer">
    <!--
        Pied de page avec affichage dynamique de l'année :
        - Si l'année actuelle est 2025, affiche simplement "2025"
        - Sinon, affiche "2025 - année courante"
    -->
    &copy; Obsek.fr -
    @if(date('Y') == 2025)
        2025
    @else
        2025 - {{ date('Y') }}
    @endif
</footer>
