<?php
namespace Civi\ActionProvider\Action\Contribution;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use CRM_ActionProvider_ExtensionUtil as E;

class Invoice extends AbstractAction {

  /**
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $output
   *
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {

    $contributionId = $parameters->getParameter('contribution_id');
    $contributionIDs = [$contributionId];
    $contactId = $parameters->getParameter('contribution_id');
    $params = ['output' => 'pdf_invoice','forPage'=>true];
    $invoicePdf = \CRM_Contribute_Form_Task_Invoice::printPDF($contributionIDs, $params, $contactId);
    $output->setParameter('invoicepdf',$invoicePdf);
  }

  /**
   * @return \Civi\ActionProvider\Parameter\SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([]);
  }

  /**
   * @return \Civi\ActionProvider\Parameter\SpecificationBag
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag([
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), TRUE),
      // It is not clear how this contact id is used. The invoice details are
      // taken from contribution. It is needed however by the library that is called.
      new Specification('contribution_id', 'Integer', E::ts('Contribution ID'), TRUE),
    ]);
    return $specs;
  }

  /**
   * @return \Civi\ActionProvider\Parameter\SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(array(
      new Specification('invoicepdf', 'String', E::ts('Invoice PDF'), false),
    ));
  }

}
