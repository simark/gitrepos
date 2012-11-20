{include
file='head.tpl'
title='Créer un entrepôt'}

{include file="beginPage.tpl"}
{include file="header.tpl"}
{include file="sidebar.tpl"}
{include file="beginContent.tpl" title="Créer un entrepôt"}
{include file="debug.tpl"}

<form method="post" action="createrepo.php">
    <table style="">
        <tr>
            <td><label>Nom : </label></td>
            <td><input type="text" name="name" placeholder="NomDuReferentiel" /></td>
        </tr>
        <tr>
            <td><label>Description : </label></td>
            <td>
                <textarea style="color: #000000;" name="description" rows="4" cols="50" placeholder="Description du référentiel."></textarea>
            </td>
        </tr>
    </table>
	<input type="Submit" width="80" value="Cr&eacute;er" name="submit" style="padding: 5px 20px;" />
</form>

{include file="endContent.tpl"}
{include file="footer.tpl"}
{include file="endPage.tpl"}
