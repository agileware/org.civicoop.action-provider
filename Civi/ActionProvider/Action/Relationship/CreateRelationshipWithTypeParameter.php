<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Relationship;

use \Civi\ActionProvider\ConfigContainer;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\SpecificationGroup;

use CRM_ActionProvider_ExtensionUtil as E;

class CreateRelationshipWithTypeParameter extends CreateRelationship {

    /**
     * Returns the specification of the configuration options for the actual action.
     *
     * @return SpecificationBag
     */
    public function getConfigurationSpecification() {
        $specs = parent::getConfigurationSpecification();
        // Remove the inherited required Relationship Type
        $specs->removeSpecificationbyName('relationship_type_id');
        // Add Relationship Type is optional
        $specs->addSpecification(new Specification('relationship_type_id', 'String', E::ts('Relationship type'), false, null, null, $this->relationshipTypes, False));

        return $specs;
    }

    /**
     * Returns the specification of the configuration options for the actual action.
     *
     * @return SpecificationBag
     * @throws \Exception
     */
    public function getParameterSpecification()
    {
        $specs = parent::getParameterSpecification();

        // Remove the inherited optional Relationship Type
        $specs->removeSpecificationbyName('relationship_type_id');
        // Add Relationship Type is required
        $specs->addSpecification(new Specification('relationship_type_id', 'String', E::ts('Relationship type'), true, null, null, $this->relationshipTypes, False));

        return $specs;
    }
}
