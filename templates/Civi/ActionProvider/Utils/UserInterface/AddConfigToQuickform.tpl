{foreach from=$actionProviderElementNames key=prefix item=action}
  {foreach from=$action item=elementName}
		  <div class="crm-section {$prefix}">
    		<div class="label">{$form.$elementName.label}</div>
    		<div class="content">{$form.$elementName.html}</div>
    		<div class="clear"></div>
  		</div>
  {/foreach}
{/foreach}