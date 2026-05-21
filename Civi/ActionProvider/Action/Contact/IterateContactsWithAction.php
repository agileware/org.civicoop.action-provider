<?php
/**
 * @author Jon Goldberg <jon@megaphonetech.com>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Contact;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use CRM_ActionProvider_ExtensionUtil as E;

class IterateContactsWithAction extends AbstractAction {

  public function getConfigurationSpecification() {
    $actionTitles = [];
    if ($this->provider) {
      $actionTitles = $this->provider->getActionTitles();
    }
    return new SpecificationBag([
      new Specification('action_type', 'String', E::ts('Action to run on each contact'), TRUE, NULL, NULL, $actionTitles, FALSE),
      // These belong to the sub-action (specified on the line above).
      new Specification('action_configuration', 'String', E::ts('Action configuration (JSON)'), FALSE),
      new Specification('action_mapping', 'String', E::ts('Action parameter mapping (JSON)'), FALSE),
    ]);
  }

  public function getParameterSpecification() {
    return new SpecificationBag([
      // Would it be crazy to take any primary key here and also specify an entity?  Out of scope for now.
      new Specification('contact_ids', 'Integer', E::ts('Contact IDs'), TRUE, NULL, NULL, NULL, TRUE),
    ]);
  }

  public function getOutputSpecification() {
    return new SpecificationBag([
      new Specification('count', 'Integer', E::ts('Number of contacts processed')),
    ]);
  }

  public function getHelpText() {
    return E::ts('Runs a single-contact action once for each contact ID in the input list. The output of the <em>first</em> contact processed is also available for mapping downstream (in addition to <code>count</code>).')
      . '<br><br>'
      . '<strong>' . E::ts('Copy configuration from an existing action in this task:') . '</strong> '
      . '<select id="iterate-copy-action-select"><option value="">' . E::ts('- select action -') . '</option></select> '
      . '<button type="button" id="iterate-copy-action-btn" class="button">' . E::ts('Copy') . '</button>'
      . '<script type="text/javascript">
CRM.$(function($) {
  var searchTaskId = $("[name=\'search_task_id\']").val();
  CRM.api3("SearchTaskAction", "get", {search_task_id: searchTaskId, options: {limit: 0}})
    .done(function(result) {
      var select = $("#iterate-copy-action-select");
      $.each(result.values, function(id, action) {
        select.append($("<option>").val(id).text(action.title || action.name));
      });
    });
  $("#iterate-copy-action-btn").on("click", function() {
    var actionId = $("#iterate-copy-action-select").val();
    if (!actionId) { return; }
    CRM.api3("SearchTaskAction", "getsingle", {id: actionId})
      .done(function(action) {
        var mapping = action.mapping || {};
        delete mapping.contact_id;
        $("[name$=\'action_configuration\']").val(JSON.stringify(action.configuration || {}));
        $("[name$=\'action_mapping\']").val(JSON.stringify(mapping));
      });
  });
});
</script>';
  }

  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $contactIds = $parameters->getParameter('contact_ids');
    // You know someone is gonna do this.
    if (!is_array($contactIds)) {
      $contactIds = array_filter(array_map('trim', explode(',', (string) $contactIds)));
    }

    $actionType = $this->configuration->getParameter('action_type');
    $actionConfigurationJson = $this->configuration->getParameter('action_configuration');
    $actionConfiguration = [];
    // I'd prefer `json_validate` but that's PHP 8.3+.
    if (!empty($actionConfigurationJson)) {
      $decoded = json_decode($actionConfigurationJson, TRUE);
      if (is_array($decoded)) {
        $actionConfiguration = $decoded;
      }
    }

    $actionMapping = [];
    $actionMappingJson = $this->configuration->getParameter('action_mapping');
    if (!empty($actionMappingJson)) {
      $decoded = json_decode($actionMappingJson, TRUE);
      if (is_array($decoded)) {
        $actionMapping = $decoded;
      }
    }

    // Always use a private batch name so the sub-action's instance and lifecycle
    // are isolated from the outer batch.
    $batchName = 'iterate_contacts_' . uniqid();

    $actionClass = $this->provider->getBatchActionByName($actionType, $actionConfiguration, $batchName);
    if (!$actionClass) {
      $output->setParameter('count', 0);
      return;
    }

    $resolvedParams = [];
    $context = $this->context ?? $parameters;
    foreach ($actionMapping as $subParamName => $sourceParamName) {
      if ($subParamName !== 'contact_id' && $context->doesParameterExists($sourceParamName)) {
        $resolvedParams[$subParamName] = $context->getParameter($sourceParamName);
      }
    }

    $subParams = $this->provider->createParameterBag();
    foreach ($resolvedParams as $name => $value) {
      $subParams->setParameter($name, $value);
    }

    $emptyBag = $this->provider->createParameterBag();
    $count = 0;

    foreach ($contactIds as $contactId) {
      $subParams->setParameter('contact_id', (int) $contactId);
      try {
        $subOutput = $actionClass->execute($subParams, $emptyBag, $emptyBag);
        // Expose the first contact's output mapping. Kinda weird, but that's what the other actions
        // that operate on multiple contacts do.
        if ($count === 0) {
          foreach ($subOutput->toArray() as $key => $value) {
            $output->setParameter($key, $value);
          }
        }
        $count++;
      }
      catch (\Exception $e) {
        // Continue processing remaining contacts on failure.
      }
    }

    $this->provider->finishBatch($batchName, TRUE);

    $output->setParameter('count', $count);
  }

}
