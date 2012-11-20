{include
    file='head.tpl'
    title='Connexion'}

<link type="text/css" rel="stylesheet" href="openid/css/openid.css" />
<script type="text/javascript" src="openid/js/openid-jquery.js"></script>
<script type="text/javascript" src="openid/js/openid-en.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        openid.init('openid_identifier');
    });
</script>

{include file="beginPage.tpl"}
{include file="header.tpl"}
{include file="beginContent.tpl" title="Connexion"}
{include file="debug.tpl"}

<!-- OpenID Selector -->
<form action="login.php" method="get" id="openid_form">
    <input type="hidden" name="action" value="verify" />
    <fieldset>
        <legend>Connectez-vous</legend>
        <div id="openid_choice">
            <p>Merci de sélectionner votre fournisseur d'identité:</p>
            <div id="openid_btns"></div>
        </div>
        <div id="openid_input_area">
            <input id="openid_identifier" name="openid_identifier" type="text" value="http://" />
            <input id="openid_submit" type="submit" value="Sign-In"/>
        </div>
        <noscript>
            <p>OpenID est un service qui vous permet de vous authentifier auprès de sites web sans avoir
                à utiliser d'autre informations de connection que celles que vous utilisez déjà.
                <a href="http://openid.net/what/">En apprendre plus.</a>.
            </p>
        </noscript>
    </fieldset>
</form>
<!-- /Simple OpenID Selector -->

{include file="endContent.tpl"}
{include file="footer.tpl"}
{include file="endPage.tpl"}