{include
file='head.tpl'
title='Mon compte'}

{include file="beginPage.tpl"}
{include file="header.tpl"}
{include file="sidebar.tpl"}
{include file="beginContent.tpl" title="Mon compte"}
{include file="debug.tpl"}

{if $msg != ''}<div style="margin: 0 auto; width: 80%;border: 1px solid #ff2233;border-radius: 5px;padding:7px;box-shadow: 10px 10px;margin-bottom: 20px;">{$msg}</div>{/if}

<form method="post" action="account.php">
    <table >
        <tr>
            <td style="width: 130px;"><label>OpenID : </label></td>
            <td><span style="font-size: 80%;">{$user->OpenID}</span></td>
        </tr>
        <tr>
            <td><label>Username : </label></td>
            <td>
                <input type="text" name="username" value="{$user->Username}" size="35" />
            </td>
        </tr>
        <tr>
            <td><label>Name : </label></td>
            <td>
                <input type="text" name="name" value="{$user->Name}" size="35" />
            </td>
        </tr>
        <tr>
            <td><label>Email : </label></td>
            <td>
                <input type="email" name="email" value="{$user->Email}" size="35" />
            </td>
        </tr>
        <tr>
            <td><label>PubKey : </label></td>
            <td>
                <textarea name="pubkey" rows="10" cols="50">{$user->PubKey}</textarea>
            </td>
        </tr>
    </table>
    <input type="Submit" width="80" value="Sauvegarder" name="save" style="padding: 5px 20px;" />
</form>

{include file="endContent.tpl"}
{include file="footer.tpl"}
{include file="endPage.tpl"}
