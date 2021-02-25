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

class SyncTagsToContact extends AbstractAction {

  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $tag_ids = $this->configuration->getParameter('tag_ids');
    if (!is_array($tag_ids) && strlen($tag_ids)) {
      $tag_ids = explode(",", $tag_ids);
    }
    foreach($tag_ids as $tag_id) {
      if ($tag_id) {
        // Check whether the contact already has this tag.
        $selectedTags = $parameters->getParameter('tag_ids');
        $currentTags = \CRM_Core_BAO_EntityTag::getTag($parameters->getParameter('contact_id'));
        if (!in_array($tag_id, $selectedTags) && in_array($tag_id, $currentTags)) {
          $tagBao = new \CRM_Core_BAO_EntityTag();
          $tagBao->entity_table = 'civicrm_contact';
          $tagBao->entity_id = $parameters->getParameter('contact_id');
          $tagBao->tag_id = $tag_id;
          if ($tagBao->find(TRUE)) {
            $tagBao->delete();
          }
        } elseif (in_array($tag_id, $selectedTags) && !in_array($tag_id, $currentTags)) {
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
      new Specification('tag_ids', 'Integer', E::ts('Tags'), true, null, 'Tag', null, true),
    ]);
  }

  /**
   * Returns a help text for this action.
   *
   * The help text is shown to the administrator who is configuring the action.
   * Override this function in a child class if your action has a help text.
   *
   * @return string|false
   */
  public function getHelpText() {
    return E::ts('With this action you can synchronize a set of contacts with the selected tags. For example: the configuration of tag ids is set to Tag 1, Tag 2 and Tag 3. The incoming parameter tags has Tag 2 and Tag 3. The Contact has Tag 1 and Tag 2. This action results in removing Tag 1 from the contact and adding Tag 3 to the contact.');
  }


}
