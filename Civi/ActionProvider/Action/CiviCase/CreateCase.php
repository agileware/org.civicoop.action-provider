<?php

namespace Civi\ActionProvider\Action\CiviCase;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class CreateCase extends AbstractAction
{

    /**
     * Returns the specification of the configuration options for the action.
     * 
     * @return SpecificationBag
     */
    public function getConfigurationSpecification() {
        /*
        $cmsUsers = civicrm_api3('Contact', 'get', [
            'uf_user' => 1,
            'return' => ["display_name", "email"],
        ]);
        if ($cmsUsers['is_error'] || empty($cmsUsers['values'])) {
            $caseManagerOptions = null;
        } else {
            $caseManagerOptions = [];
            foreach ($cmsUsers['values'] as $user) {
                $nameAndEmail = "${user['display_name']} <${user['email']}>";
                $caseManagerOptions[$user['contact_id']] = $nameAndEmail;
            }
        }
        */
        /**
         * The parameters given to the Specification object are:
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
                new Specification('case_type_id', 'Integer', E::ts('Case Type'), true, null, 'CaseType', null, FALSE),
                new Specification('subject', 'String', E::ts('Subject'), true, null, null, null, FALSE),
                //new Specification('creator_id', 'Integer', E::ts('Case Manager'), true, null, null, $caseManagerOptions, FALSE),
                new Specification('creator_id', 'Integer', E::ts('Case Manager'), true, null, 'Contact', null, FALSE),
            ]
        );
    }

    /**
     * Returns the specification of the configuration options for the action.
     * 
     * @return SpecificationBag
     */
    public function getParameterSpecification()
    {
        return new SpecificationBag(
            [new Specification('contact_id', 'Integer', E::ts('Contact ID'), true, null, null, null, FALSE)]
        );
    }

    /**
     * Returns the specification of the output parameters of this action.
     * 
     * @return SpecificationBag
     */
    public function getOutputSpecification()
    {
        return new SpecificationBag(
            [new Specification('case_id', 'Integer', E::ts('Case ID'), false)]
        );
    }

    /**
     * Run the action
     * 
     * @param ParameterInterface $parameters
     *   The parameters to this action.
     * @param ParameterBagInterface $output
     *   The parameters this action can send back 
     * @return void
     */
    protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output)
    {
        // Get the contact.
        $contact_id = $parameters->getParameter('contact_id');

        // Get the case type and subject.
        $case_type_id = $this->configuration->getParameter('case_type_id');
        $subject = $this->configuration->getParameter('subject');
        $creator_id = $this->configuration->getParameter('creator_id');

        // Create the case through an API call.
        try {
            $result = civicrm_api3('Case', 'create', array(
                'contact_id' => $contact_id,
                'case_type_id' => $case_type_id,
                'subject' => $subject,
                'creator_id' => $creator_id,
            ));
        } catch (Exception $e) {
            throw new \Civi\ActionProvider\Action\Exception\ExecutionException(E::ts('Could not create case'));
        }
        $case_id = \CRM_Utils_Array::value('id', $result);
        $output->setParameter('case_id', $case_id);
    }
}
