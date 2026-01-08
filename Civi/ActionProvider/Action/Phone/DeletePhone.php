<?php

namespace Civi\ActionProvider\Action\Phone;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Action\Contact\ContactActionUtils;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\OptionGroupSpecification;
use CRM_ActionProvider_ExtensionUtil as E;

class DeletePhone extends AbstractAction {

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $contactId = $parameters->getParameter('contact_id');
    $phone = $parameters->getParameter('phone');
    try {
        $retrievedPhone = \Civi\Api4\Phone::get(FALSE)
            ->addWhere('contact_id', '=', $contactId)
            ->addWhere('phone', '=', $phone)
            ->execute()->first();
        if ($retrievedPhone['id']) {
            \Civi\Api4\Phone::delete(FALSE)
                ->addWhere('id' , '=', $retrievedPhone['id'])
                ->execute();
            $message = "Deleted phone number " . $phone . " from contact ID " . $contactId;
        }
        else {
            $message = "Phone number " . $phone . " from contact ID " . $contactId . "  not found";
        }
    }
    catch (\CRM_Core_Exception $ex) {
        \Civi::log()->error("Error trying to get or delete phone " . $phone
            . "  from contact ID " . $contactId . " in " . __METHOD__
            . ", error message from API4 Phone get or delete: " . $ex->getMessage());
        $message = "Could not delete phone: " . $phone . " from Contact ID " . $contactId;
    }
    $output->setParameter('phone_message', $message);
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification(): SpecificationBag {
    return new SpecificationBag([]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification(): SpecificationBag {
    return new SpecificationBag(array(
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), TRUE),
      new Specification('phone', 'String', E::ts('Phone'), TRUE),
    ));
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification(): SpecificationBag {
    return new SpecificationBag([
        new Specification('phone_message', 'String', E::ts('Output message'), FALSE)
    ]);
  }



}
