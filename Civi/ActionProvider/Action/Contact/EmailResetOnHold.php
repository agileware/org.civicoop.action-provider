<?php
declare(strict_types = 1);

namespace Civi\ActionProvider\Action\Contact;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\Api4\Email;
use CRM_ActionProvider_ExtensionUtil as E;

final class EmailResetOnHold extends AbstractAction {

  /**
   * @inheritDoc
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $contactId = $parameters->getParameter('contact_id');
    $email = $parameters->getParameter('email');

    $updateEmail = Email::update(FALSE)
      ->addValue('on_hold', 0)
      ->addWhere('contact_id', '=', $contactId);

    if (NULL !== $email) {
      $updateEmail->addWhere('email', '=', $email);
    }

    $updateEmail->execute();
  }

  /**
   * @inheritDoc
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag();
  }

  /**
   * @inheritDoc
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag([
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), TRUE),
    ]);
    ContactActionUtils::createEmailParameterSpecification($specs);

    return $specs;
  }

}
