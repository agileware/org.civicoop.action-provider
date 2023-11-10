<?php

namespace Civi\ActionProvider\Action\CiviCase;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class CreateOrUpdateRole extends AbstractAction {

  private function relationshipTypes() {
    $options = [];
    $result = civicrm_api3('RelationshipType', 'get', [
      'sequential' => 1,
      'return' => ["id", "label_a_b", "label_b_a"],
      'options' => ['limit' => 0]
    ]);
    foreach ($result['values'] as $value) {
      $options["${value['id']}_a_b"] = "{$value['label_a_b']} (a->b)";
      $options["${value['id']}_b_a"] = "{$value['label_b_a']} (b->a)";
    }
    uasort($options, function ($a, $b) {
      return $a >= $b;
    });
    return $options;
  }

  /**
   * Returns the specification of the configuration options for the action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    /**
     * The parameters given to the Specification object are:
     *
     * @param string $name
     * @param string $dataType
     * @param string $title
     * @param bool $required
     * @param mixed $defaultValue
     * @param string|null $fkEntity
     * @param array $options
     * @param bool $multiple
     */
    return new SpecificationBag(
      [
        new Specification('relationship_type', 'String', E::ts('RelationShip'), TRUE, NULL, NULL, $this->relationshipTypes(), FALSE),
        new Specification('check_permissions', 'Boolean', E::ts('Check Api Permissions'), TRUE, TRUE, NULL, NULL, FALSE),
        new Specification('include_inactive', 'Boolean', E::ts('Include inactive roles'), TRUE, TRUE, NULL, NULL, FALSE),
      ]
    );
  }

  /**
   * Returns the specification of the configuration options for the action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('contact_id_a', 'Integer', E::ts('Contact ID: Near Side'), TRUE, NULL, NULL, NULL, FALSE),
      new Specification('contact_id_b', 'Integer', E::ts('Contact ID: Far Side (defaults to client)'), FALSE, NULL, NULL, NULL, FALSE),
      new Specification('case_id', 'Integer', E::ts('Case ID'), TRUE, NULL, NULL, NULL, FALSE),
    ]);
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(
      [new Specification('relationship_id', 'Integer', E::ts('Relationship ID'), FALSE)]
    );
  }

  /**
   * Run the action
   *
   * @param ParameterInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   *
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    // Get the contact.
    $contact_id_a = $parameters->getParameter('contact_id_a');
    $case_id = $parameters->getParameter('case_id');
    $checkPermissions = $this->configuration->getParameter('check_permissions');
    $includeInactive = $this->configuration->getParameter('include_inactive');
    $relationshipType = $this->configuration->getParameter('relationship_type');
    [$relTypeId, $b, $a] = explode('_', $relationshipType);

    /* 
     * get contact b or, if not provided, get the case client as contact b
     */


    if ($parameters->doesParameterExists('contact_id_b')) {
      $contact_id_b = $parameters->getParameter('contact_id_b');
    }
    else {
      $caseContacts = civicrm_api4('CaseContact', 'get', [
        'select' => [
          'contact_id',
        ],
        'where' => [
          ['case_id', '=', $case_id],
        ],
        'limit' => 1,
        'checkPermissions' => $checkPermissions,
      ]);

      $contact_id_b = $caseContacts[0]['contact_id'];
    }

    /*
     * check if role already exists
     */
    $apiParams = [
      'select' => [
        'id',
      ],
      'where' => [
        ['case_id', '=', $case_id],
        ['relationship_type_id', '=', $relTypeId]
      ],
      'limit' => 1,
      'checkPermissions' => $checkPermissions
    ];
    if (!$includeInactive) {
      $apiParams['where'][] = ['is_active', '=', TRUE];
    }
    $relationshipId = civicrm_api4('Relationship', 'get', $apiParams);

    $apiParams = ['checkPermissions' => $checkPermissions];
    $apiParams['values'] = [
      'case_id' => $case_id,
      'relationship_type_id' => $relTypeId,
      'is_active' => TRUE
    ];
    if ($a === 'a') {
      $apiParams['values']['contact_id_a'] = $contact_id_a;
      $apiParams['values']['contact_id_b'] = $contact_id_b;
    }
    else {
      $apiParams['values']['contact_id_a'] = $contact_id_a;
      $apiParams['values']['contact_id_b'] = $contact_id_b;
    }

    /*
     * create a new relationship, if none was found
     */
    if (!isset($relationshipId[0]['id'])) {
      try {
        $result = civicrm_api4('Relationship', 'create', $apiParams);
        $output->setParameter('relationship_id', $result[0]['id']);
      }
      catch (Exception $e) {
        throw new \Civi\ActionProvider\Action\Exception\ExecutionException(E::ts('Could not create new relationship'));
      }
    }
    else {
      $apiParams['where'] = [
        ['id', '=', $relationshipId[0]['id']]
      ];
      try {
        $result = civicrm_api4('Relationship', 'update', $apiParams);
        $output->setParameter('relationship_id', $result[0]['id']);
      }
      catch (Exception $e) {
        throw new \Civi\ActionProvider\Action\Exception\ExecutionException(E::ts('Could not update relationship'));
      }
    }


  }
}
