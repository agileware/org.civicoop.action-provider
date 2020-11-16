{crmScope extensionKey='action-provider'}
{assign var='actionProviderMappingFields' value=$actionProviderMappingFields.$prefix}
{assign var='actionProviderMappingDescriptions' value=$actionProviderMappingDescriptions.$prefix}
{assign var='actionProviderGroupedMappingFields' value=$actionProviderGroupedMappingFields.$prefix}

  <div class="crm-accordion-wrapper">
    <div class="crm-accordion-header">
      {$title}
    </div>
    <div class="crm-accordion-body">
      {foreach from=$actionProviderMappingFields item=elementName}
        <div class="crm-section">
          <div class="label">{$form.$elementName.label}</div>
          <div class="content">
            {$form.$elementName.html}
            {if ($actionProviderMappingDescriptions.$elementName)}
              <br /><span class="description">{$actionProviderMappingDescriptions.$elementName}</span>
            {/if}
          </div>
          <div class="clear"></div>
        </div>
      {/foreach}

      {foreach from=$actionProviderGroupedMappingFields item=group}
        <div class="crm-accordion-wrapper collapsed">
          <div class="crm-accordion-header">{$group.title}</div>
          <div class="crm-accordion-body">
            {foreach from=$group.fields item=elementName}
              <div class="crm-section">
                <div class="label">{$form.$elementName.label}</div>
                <div class="content">
                  {$form.$elementName.html}
                  {if ($actionProviderMappingDescriptions.$elementName)}
                    <br /><span class="description">{$actionProviderMappingDescriptions.$elementName}</span>
                  {/if}
                </div>
                <div class="clear"></div>
              </div>
            {/foreach}
          </div>
        </div>
      {/foreach}
    </div>
  </div>
{/crmScope}
