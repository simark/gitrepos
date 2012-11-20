{if count($errors) > 0}
<ul class="errorbox">
    {foreach $errors as $e}
        <li>{$e}</li>
    {/foreach}
</ul>
{/if}