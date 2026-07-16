<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Exception\InvalidParameterException;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use Civi\ActionProvider\Utils\CustomField;
use CRM_ActionProvider_ExtensionUtil as E;

class FindHouseholdByName extends AbstractAction {

  private const FUZZY_WARNING_LIMIT = 1000;

	/**
	 * Run the action
	 *
	 * @param ParameterBagInterface $parameters
	 *   The parameters to this action.
	 * @param ParameterBagInterface $output
	 * 	 The parameters this action can send back
	 * @return void
	 */
	protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
	  try {
      $contact_sub_type = $this->configuration->getParameter('contact_sub_type');
      $household_name = $parameters->getParameter('household_name');
      $name_length_limit = $this->configuration->getParameter('name_length_limit');
      $find_newest = $this->configuration->getParameter('find_newest');

      $query = \Civi\Api4\Contact::get(FALSE)
        ->selectRowCount()
        ->addSelect('id', 'display_name')
        ->addWhere('contact_type', '=', 'Household');

      if ($contact_sub_type) {
        $query->addWhere('contact_sub_type', '=', $contact_sub_type);
      }
      if ($name_length_limit) {
        $name_part = mb_substr($household_name, 0, $name_length_limit);
        $query->addWhere('household_name', 'LIKE', $name_part . '%');
      }
      else {
        $query->addWhere('household_name', '=', $household_name);
        $query->setLimit(1);
      }

      if ($find_newest) {
        $query->addOrderBy('id', 'DESC');
      }
      else {
        // (old) default
        $query->addOrderBy('id');
      }
      $query->setLimit(self::FUZZY_WARNING_LIMIT);
      $contacts = $query->execute();

      $contact_id = '';

      if ($name_length_limit) {
        if ($contacts->countMatched() > self::FUZZY_WARNING_LIMIT) {
          \Civi::log()->warning('Action FindOrganizationByName: too many results for the fuzzy search. This is a potential performance problem. Try to increase the "Organisation name length".');
        }

        $top_score = PHP_INT_MAX;
        foreach ($contacts as $contact) {
          $score = levenshtein($household_name, $contact['display_name']);
          \Civi::log()->info($contact['display_name'] . " (" . $contact['id'] . "), score: $score");
          if ($score < $top_score) {
            $contact_id = $contact['id'];
            $top_score = $score;
          }
        }
      }
      else {
        if ($contacts->count() > 0) {
          $contact_id = $contacts->first()['id'];
        }
      }
      $output->setParameter('contact_id', $contact_id);
    } catch (\Exception $e) {
	    // Do nothing.
    }
	}

  /**
	 * Returns the specification of the configuration options for the actual action.
	 *
	 * @return SpecificationBag
	 */
	public function getConfigurationSpecification() {
    $contactSubTypes = array();
    $contactSubTypesApi = civicrm_api3('ContactType', 'get', array('parent_id' => 'Household', 'options' => array('limit' => 0)));
    $contactSubTypes[''] = E::ts(' - Select - ');
    foreach($contactSubTypesApi['values'] as $contactSubType) {
      $contactSubTypes[$contactSubType['name']] = $contactSubType['label'];
    }

    $fuzzySpec = new Specification('name_length_limit', 'Integer', E::ts('Household name length (enables fuzzy search)'), false, 0, null, null, FALSE);
    $fuzzySpec->setDescription(E::ts('This switches to a fuzzy search by matching only the first characters against the household name. Then the closest match is selected using the full length. Beware: This is slower than the normal search. Do not set this to a very small value if you have many households!'));

    return new SpecificationBag([
      new Specification('contact_sub_type', 'String', E::ts('Contact sub type'), false, null, null, $contactSubTypes, FALSE),
      $fuzzySpec,
      new Specification('find_newest', 'Boolean', E::ts('Find newest household'), true, 0, null, null, FALSE),
    ]);
	}

	/**
	 * Returns the specification of the parameters of the actual action.
	 *
	 * @return SpecificationBag
	 */
	public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('household_name', 'String', E::ts('Household name'), true),
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
			new Specification('contact_id', 'Integer', E::ts('Contact ID'), true)
		));
	}

}
