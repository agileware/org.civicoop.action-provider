<?php
/**
 * @author Simon Hermann <simon.hermann@civiservice.de>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Generic;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Utils\CustomField;

use Civi\Api4\Note;
use CRM_ActionProvider_ExtensionUtil as E;

class AddNote extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $entities = [
      'civicrm_contact' => 'civicrm_contact',
      'civicrm_contribution' => 'civicrm_contribution',
      'civicrm_participant' => 'civicrm_participant',
      'civicrm_relationship' => 'civicrm_relationship'
    ];
    return new SpecificationBag([
      new Specification(
        'civi_entity',
        'String',
        E::ts('Enter a civicrm entity type where the note will be added to.'),
        TRUE,
        '',
        '',
        $entities
      ),
      new Specification('note', 'Text', E::ts('The contents of the note.')),
    ]);
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   * @throws \Exception
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('civi_entity_id', 'Integer', E::ts('The ID of the entity the note will be added to.'), TRUE),
      new Specification('note', 'Text', E::ts('The contents of the note.')),
    ));
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(array(
      new Specification('note_id', 'Integer', E::ts('Note ID')),
    ));
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
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $note = "";
    if ($this->configuration->getParameter('note')) {
      $note = $this->configuration->getParameter('note');
    }
    // Overwrite with value from parameters if given.
    if ($parameters->doesParameterExists('note')) {
      $note = $parameters->getParameter('note');
    }
    $entity = $this->configuration->getParameter('civi_entity');
    $id = $parameters->getParameter('civi_entity_id');

    $result = Note::create()
        ->addValue('entity_table', $entity)
        ->addValue('entity_id', $id)
        ->addValue('note', $note)
        ->execute();

    $output->setParameter('note_id', $result['id']);
  }

}
