{include
file='head.tpl'
title='Mes entrepôts'}

{include file="beginPage.tpl"}
{include file="header.tpl"}
{include file="sidebar.tpl"}
{include file="beginContent.tpl" title="$reponame"}
{include file="debug.tpl"}

{if $repo}
	<h2>Commande pour cloner l'entrepôt</h2>
	<p><span style="color: white; background-color: grey; padding: 4px; font-family: monospace;">git clone ssh://git@git.aep.polymtl.ca/{$reponame}</span></p>
	<h2>Permissions</h2>
	<form action="repodetail.php?r={$reponame}" method="post" style="padding: 0 15px;">
		<table>
		{foreach $repo as $perm}
			<tr>
			<td style="text-align: right;">{$perm.name} &lt; {$perm.username} &gt;</td>
			<td>
			<select {if $perm.is_owner}disabled="" {/if} name="{$perm.uid}">
                <option value="1" {if $perm.pid == 1}selected="selected" {/if}>R</option>
                <option value="2" {if $perm.pid == 2}selected="selected" {/if}>RW</option>
                <option value="3" {if $perm.pid == 3}selected="selected" {/if}>RW+</option>
                <option value="-1">Supprimer</option>
			</select>
			</td>
                <td> est administrat(eur|rice) <input name="_{$perm.uid}" type="checkbox" {if $perm.is_owner}disabled="" {/if} {if $perm.is_admin}checked=""{/if} style="height: 20px;width: 20px;"/></td>
			</tr>
		{/foreach}
		<tr>
		<td>
		<input type="text" name="usernameToAdd" size="10" />
		</td>
		<td>
		<select name="permLevel">
			<option value="1">R</option>
			<option value="2">RW</option>
			<option value="3+">RW+</option>
		</select>
		</td>
        <td> est administrat(eur|rice) <input name="is_admin" type="checkbox" style="height: 20px;width: 20px;"/></td>
		</tr>
		</table>
		<input type="hidden" name="repoId" value="{$repo[0].id}" />
		<p>
			<input type="submit" value="Modifier" />
		</p>
		<p>Les permissions prennent effet toutes les deux minutes, au besoin.</p>
		<p>Temps du serveur: {$server_time|date_format:"%Y-%m-%d %H:%M:%S"}</p>
		<p>Dernière génération des permissions: {$perm_gen_time|date_format:"%Y-%m-%d %H:%M:%S"}</p>
	</form>
{else}
Cet entrepôt n'existe pas.
{/if}

{include file="endContent.tpl"}
{include file="footer.tpl"}
{include file="endPage.tpl"}
