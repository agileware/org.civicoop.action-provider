{crmScope extensionKey='action-provider'}
{assign var='actionProviderMappingFields' value=$actionProviderMappingFields.$prefix}
{assign var='actionProviderMappingDescriptions' value=$actionProviderMappingDescriptions.$prefix}
{assign var='actionProviderGroupedMappingFields' value=$actionProviderGroupedMappingFields.$prefix}
{assign var='actionProviderCollectionMappingFields' value=$actionProviderCollectionMappingFields.$prefix}
{if count($actionProviderMappingFields) || count($actionProviderGroupedMappingFields) || count($actionProviderCollectionMappingFields)}
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
            {if isset($actionProviderMappingDescriptions.$elementName)}
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
                  {if isset($actionProviderMappingDescriptions.$elementName)}
                    <br /><span class="description">{$actionProviderMappingDescriptions.$elementName}</span>
                  {/if}
                </div>
                <div class="clear"></div>
              </div>
            {/foreach}
          </div>
        </div>
      {/foreach}

      {foreach from=$actionProviderCollectionMappingFields item=group}
        <div class="crm-section">
          {assign var='countName' value="`$group.name`Count"}
          <input type="hidden" name="{$countName}" value="{$group.count}" id="{$countName}" />
          <h4>{$group.title}</h4>
          {capture assign="actionProviderCollectionMappingTemplate"}{strip}
            <tr>
                {foreach from=$group.fields item=elementName}
                  <td>
                      {$form.$elementName[0].html}
                      {if isset($actionProviderMappingDescriptions.$elementName)}
                        <br /><span class="description">{$actionProviderMappingDescriptions.$elementName}</span>
                      {/if}
                  </td>
                  <td>
                    <a href="#" onclick="return removeItem_{$group.name}(this);">
                      <i class="crm-i fa-trash" aria-hidden="true">&nbsp;</i>{ts}Remove{/ts}
                    </a>
                  </td>
                {/foreach}
            </tr>
          {/strip}{/capture}

          <table id="actionProviderCollectionMapping{$group.name}">
            <tr>
              {foreach from=$group.fields item=elementName}
                <th>{$form.$elementName[0].label}
                    {if $group.is_required.$elementName}<span class="crm-marker">*</span>{/if}
                </th>
              {/foreach}
              <th>&nbsp;</th>
            </tr>
            {foreach from=$group.elements item=elementIndex}
              <tr>
                  {foreach from=$group.fields item=elementName}
                    <td>
                        {$form.$elementName[$elementIndex].html}
                        {if isset($actionProviderMappingDescriptions.$elementName)}
                          <br /><span class="description">{$actionProviderMappingDescriptions.$elementName}</span>
                        {/if}
                    </td>
                    <td>
                      <a href="#" onclick="return removeItem_{$group.name}(this);">
                        <i class="crm-i fa-trash" aria-hidden="true">&nbsp;</i>{ts}Remove{/ts}
                      </a>
                    </td>
                  {/foreach}
              </tr>
            {/foreach}
          </table>
          <div class="crm-submit-buttons">
            <script type="text/javascript">
              {literal}
                function removeItem_{/literal}{$group.name}{literal}(element) {
                  CRM.$(element).parent().parent().remove();
                  var currentRowCount = CRM.$('#actionProviderCollectionMapping{/literal}{$group.name}{literal} tr').length -1;
                  CRM.$('#{/literal}{$group.name}{literal}Count').val(currentRowCount);
                  return false;
                }
                function addItem_{/literal}{$group.name}{literal}() {
                  var template = CRM.$('{/literal}{$actionProviderCollectionMappingTemplate|escape:javascript}{literal}');
                  var min = {/literal}{if $group.max}{$group.min}{else}false{/if}{literal};
                  var max = {/literal}{if $group.max}{$group.max}{else}false{/if}{literal};
                  var currentRowCount = CRM.$('#actionProviderCollectionMapping{/literal}{$group.name}{literal} tr').length -1;
                  var newRowCount = currentRowCount + 1;
                  template.addClass('row-'+newRowCount);
                  if (!max || currentRowCount < max) {
                    CRM.$('#actionProviderCollectionMapping{/literal}{$group.name}{literal}').append(template);
                    CRM.$('#actionProviderCollectionMapping{/literal}{$group.name}{literal} tr.row-'+newRowCount+' select.crm-select2').each(function (index, element) {
                      CRM.$(element).attr('name', CRM.$(element).data('single-name') + '[' + newRowCount + ']');
                      CRM.$(element).crmSelect2();
                    });
                    CRM.$('#{/literal}{$group.name}{literal}Count').val(newRowCount);
                  }
                }
              {/literal}
            </script>
            <button class="crm-button" onclick="addItem_{$group.name}(); return false;"><i class="crm-i fa-plus" aria-hidden="true">&nbsp;</i>{ts}Add item{/ts}</button></div>
        </div>
      {/foreach}
    </div>
  </div>
{/if}
{/crmScope}
