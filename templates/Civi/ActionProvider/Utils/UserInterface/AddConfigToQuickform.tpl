<div class="crm-accordion-wrapper">
  <div class="crm-accordion-header">
    {$title}
  </div>
  <div class="crm-accordion-body">
    {foreach from=$actionProviderElementNames item=prefixedElements key=prefix}
      {foreach from=$prefixedElements item=elementName}
        <div class="crm-section">
          <div class="label">{$form.$elementName.label}</div>
          <div class="content">
            {if ($actionProviderElementPreHtml.$elementName)}
              {$actionProviderElementPreHtml.$elementName}
            {/if}
            {$form.$elementName.html}
            {if ($actionProviderElementDescriptions.$elementName)}
              <br /><span class="description">{$actionProviderElementDescriptions.$elementName}</span>
            {/if}
            {if ($actionProviderElementPostHtml.$elementName)}
              {$actionProviderElementPostHtml.$elementName}
            {/if}
          </div>
          <div class="clear"></div>
        </div>
      {/foreach}
    {/foreach}
  </div>
</div>
