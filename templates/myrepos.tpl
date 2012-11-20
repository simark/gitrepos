{include
    file='head.tpl'
    title='Mes entrepôts'}

{include file="beginPage.tpl"}
{include file="header.tpl"}
{include file="sidebar.tpl"}
{include file="beginContent.tpl" title="Mes entrepôts"}
{include file="debug.tpl"}

<h2>Mes entrepôts</h2>
{if count($adminRepoList) > 0}
	<ul>
	{foreach $adminRepoList as $repo}
		<li>
            <a href="repodetail.php?r={$repo->Name}">{$repo->Name}</a>
            <details>{$repo->Description}</details>
        </li>
	{/foreach}
	</ul>
{else}
	<p>Vous ne possédez aucun entrepôt.</p>
{/if}

<h2>Mes permissions dans les entrepôts des autres</h2>
{if count($repoList) > 0}
	<ul>
	{foreach $repoList as $repo}
		<li>{$repo->OPermission->Perm} sur {$repo->Name}</a><br />
		<span style="color: white; background-color: grey; padding: 4px; font-family: monospace;">git clone ssh://git@git.aep.polymtl.ca/{$repo->Name}</span><br />
        <details>{$repo->Description}</details>
		</li>
	{/foreach}
	</ul>
{/if}

{include file="endContent.tpl"}
{include file="footer.tpl"}
{include file="endPage.tpl"}
