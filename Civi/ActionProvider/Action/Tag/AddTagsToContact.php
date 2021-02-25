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

class AddTagsToContact extends AbstractAction {

  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $tag_ids = $this->configuration->getParameter('tag_ids');
    if (!is_array($tag_ids) && strlen($tag_ids)) {
      $tag_ids = explode(",", $tag_ids);
    }
    foreach($tag_ids as $tag_id) {
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
  }

  public function getConfigurationSpecification() {
    return new SpecificationBag([
     new Specification('tag_ids', 'Integer', E::ts('Tags'), true, null, 'Tag', null, true),
    ]);
  }

  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true),
    ]);
  }

}
