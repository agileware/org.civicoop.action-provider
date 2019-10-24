<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Tag;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

use Civi\FormProcessor\API\Exception;
use CRM_ActionProvider_ExtensionUtil as E;

class AddTagToContact extends AbstractAction {

  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $tag = $parameters->getParameter('tag');
    $tag_id = false;
    try {
      $tag_id = civicrm_api3('Tag', 'getvalue', ['return' => 'id', 'name' => $tag, 'used_for' => 'Contacts']);
    } catch (\Exception $e) {
      $result = civicrm_api3('Tag', 'create', ['name' => $tag, 'used_for' => 'Contacts']);
      $tag_id = $result['id'];
    }
    if ($tag_id) {
      // Check whether the contact already has this tag.
      $tags = \CRM_Core_BAO_EntityTag::getTag($parameters->getParameter('contact_id'));
      if (!in_array($tag_id, $tags)) {
        civicrm_api3('EntityTag', 'create', [
          'tag_id' => $tag_id,
          'entity_id' => $parameters->getParameter('contact_id'),
          'entity_table' => 'civicrm_contact',
        ]);
      }
    }
  }

  public function getConfigurationSpecification() {
    return new SpecificationBag();
  }

  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true),
      new Specification('tag', 'String', E::ts('Tag'), true)
    ]);
  }

}
