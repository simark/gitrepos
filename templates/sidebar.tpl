<div class="sidebar1">
    <ul class="nav">
        {if $user}
        {if $user->Username != ''}<li><a href="myrepos.php">Mes entrepôts</a></li>{/if}
        {if $user->IsStudent}<li><a href="createrepo.php">Créer un entrepôt</a></li>{/if}
        {if $user->Username != ''}<li><a href="account.php">Mon compte</a></li>{/if}
        <li><a href="logout.php">Déconnexion</a></li>
        {else}
        <li><a href="login.php">Connexion</a></li>
        {/if}
    </ul>
    <p>
        De l'aide est disponible <a href="#">ici</a> et <a href="#">ici</a>
    </p>
    <p>
        {if $user && $user->Username}
        Connecté en tant que <strong>{$user->Name}</strong> sous le pseudonyme <strong>{$user->Username}</strong>
        {else}
        Vous n'êtes pas connecté.
        {/if}
    </p>
</div>