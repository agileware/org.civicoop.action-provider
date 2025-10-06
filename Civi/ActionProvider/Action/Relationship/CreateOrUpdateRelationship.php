<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Relationship;

use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationGroup;

use Civi\Api4\Relationship;
use CRM_ActionProvider_ExtensionUtil as E;

class CreateOrUpdateRelationship extends CreateRelationship {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
    public function getConfigurationSpecification()
    {
        $specs = parent::getConfigurationSpecification();
        $specs->addSpecification(new Specification('also_update_inactive', 'Boolean', E::ts('Update inactive relationships'), false, 0, null, null, false));

        return $specs;
    }

  /**
   * Find existing relationship
   *
   * @param $contact_id_a
   * @param $contact_id_b
   * @param $type_id
   * @param bool $also_inactive
   *
   * @return mixed
   */
  protected function findExistingRelationshipId($contact_id_a, $contact_id_b, $type_id, $also_inactive = FALSE) {
      $relationshipQuery = Relationship::get(FALSE)
      ->addSelect('id')
      ->addWhere('contact_id_a', '=', $contact_id_a)
      ->addWhere('contact_id_b', '=', $contact_id_b)
      ->addWhere('relationship_type_id', '=', $type_id)
      ->addOrderBy('is_active', 'DESC');

    if (!$also_inactive) {
      $relationshipQuery->addWhere('is_active', '=', TRUE);
    }
    try {
      return $relationshipQuery->execute()->first()['id'] ?? FALSE;
    } catch (\Exception $e) {
      // Do nothing
      return FALSE;
    }
  }

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   * @throws \Exception
   */
    protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output)
    {
        $relationship_type_id = $this->relationshipTypeIds[$this->configuration->getParameter('relationship_type_id')];
        $alsoUpdateInactiveOne = false;
        if ($this->configuration->doesParameterExists('also_update_inactive') && $this->configuration->getParameter('also_update_inactive')) {
            $alsoUpdateInactiveOne = true;
        }
        $relationship_id = $this->findExistingRelationshipId($parameters->getParameter('contact_id_a'), $parameters->getParameter('contact_id_b'), $relationship_type_id, $alsoUpdateInactiveOne);
        if ($relationship_id) {
            $relationshipParams['id'] = $relationship_id;
        }

        if ($this->configuration->doesParameterExists('is_permission_a_b') && $this->configuration->getParameter('is_permission_a_b')) {
            $relationshipParams['is_permission_a_b'] = $this->configuration->getParameter('is_permission_a_b');
        }
        if ($this->configuration->doesParameterExists('is_permission_b_a') && $this->configuration->getParameter('is_permission_b_a')) {
            $relationshipParams['is_permission_b_a'] = $this->configuration->getParameter('is_permission_b_a');
        }
        if ($parameters->doesParameterExists('is_permission_a_b') && $parameters->getParameter('is_permission_a_b')) {
            $relationshipParams['is_permission_a_b'] = $parameters->getParameter('is_permission_a_b');
        }
        if ($parameters->doesParameterExists('is_permission_b_a') && $parameters->getParameter('is_permission_b_a')) {
            $relationshipParams['is_permission_b_a'] = $parameters->getParameter('is_permission_b_a');
        }

        $relationshipParams['description'] = $parameters->getParameter('description');
        $relationshipParams['case_id'] = $parameters->getParameter('case_id');
        $relationshipParams['contact_id_a'] = $parameters->getParameter('contact_id_a');
        $relationshipParams['contact_id_b'] = $parameters->getParameter('contact_id_b');
        $relationshipParams['start_date'] = $parameters->getParameter('start_date');
        $relationshipParams['end_date'] = $parameters->getParameter('end_date');
        $relationshipParams['relationship_type_id'] = $relationship_type_id;
        $relationshipParams['is_active'] = '1';

        if ($this->configuration->getParameter('set_start_date') && !$relationship_id) {
            $today = new \DateTime();
            $relationshipParams['start_date'] = $today->format('Ymd');
        } else if ($parameters->doesParameterExists('start_date')) {
            $relationshipParams['start_date'] = $parameters->getParameter('start_date');
        }

        $relationshipParams['custom'] = [];
        foreach ($this->getParameterSpecification() as $spec) {
            if ($spec instanceof SpecificationGroup) {
                foreach ($spec->getSpecificationBag() as $subSpec) {
                    if (stripos($subSpec->getName(), 'custom_') === 0 && $parameters->doesParameterExists($subSpec->getName())) {
                        list($customFieldID, $customValueID) = \CRM_Core_BAO_CustomField::getKeyID($subSpec->getApiFieldName(), TRUE);
                        $value = $parameters->getParameter($subSpec->getName());
                        if (is_array($value)) {
                            $value = \CRM_Core_DAO::VALUE_SEPARATOR . implode(\CRM_Core_DAO::VALUE_SEPARATOR, $value) . \CRM_Core_DAO::VALUE_SEPARATOR;
                        }
                        \CRM_Core_BAO_CustomField::formatCustomField($customFieldID, $relationshipParams['custom'], $value, 'Relationship', $customValueID);
                    }
                }
            } elseif (stripos($spec->getName(), 'custom_') === 0) {
                if ($parameters->doesParameterExists($spec->getName())) {
                    list($customFieldID, $customValueID) = \CRM_Core_BAO_CustomField::getKeyID($spec->getApiFieldName(), TRUE);
                    $value = $parameters->getParameter($spec->getName());
                    if (is_array($value)) {
                        $value = \CRM_Core_DAO::VALUE_SEPARATOR . implode(\CRM_Core_DAO::VALUE_SEPARATOR, $value) . \CRM_Core_DAO::VALUE_SEPARATOR;
                    }
                    \CRM_Core_BAO_CustomField::formatCustomField($customFieldID, $relationshipParams['custom'], $value, 'Relationship', $customValueID);
                }
            }
        }

        try {
            // Do not use api as the api checks for an existing relationship.
            $relationship = \CRM_Contact_BAO_Relationship::add($relationshipParams);
            $relationship_id = $relationship->id;
          // Update employee_id on contact if required
          EmployerId::updateEmployerId($relationshipParams);

            // Update the related memberships
            $contact_ids = [
                'contactTarget' => $relationshipParams['contact_id_b'],
                'contact' => $relationshipParams['contact_id_a'],
            ];
            // When the relationship end date is set to 'null' related memberships are deleted
            if ($relationshipParams['end_date'] == 'null') {
                $relationshipParams['end_date'] = null;
            }
            $action = !empty($relationshipParams['id']) ? \CRM_Core_Action::UPDATE : \CRM_Core_Action::ADD;
            \CRM_Contact_BAO_Relationship::relatedMemberships($relationshipParams['contact_id_a'], $relationshipParams, $contact_ids, $action, TRUE);

            $output->setParameter('id', $relationship_id);
        } catch (\Exception) {
            // Do nothing.
        }
    }

}
