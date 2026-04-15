<?php
/**
 * @author  Agileware <projects@agileware.com.au>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\PaymentToken;

use DateTime;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\Specification;
use Civi\Api4\ContributionRecur;

use CRM_ActionProvider_ExtensionUtil as E;

class ResetFailedContributionRecursForPaymentToken extends AbstractAction {
    /**
     * Contribution status machine names that may be processed
     */
    const PROCESS_STATUS_NAMES = [ 'Failed', 'Failing', ];

    protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output)
    {
        $payment_token_id = $parameters->getParameter('payment_token_id');

        $today = new DateTime('today');

        $reset_schedule = $parameters->getParameter('reset_next_scheduled_date') ?? $this->getConfiguration()->getParameter('reset_next_scheduled_date_default');
        $advance_schedule = $parameters->getParameter('advance_next_scheduled_date') ?? $this->getConfiguration()->getParameter('advance_next_scheduled_date_default');

        // When configured to advance the scheduled date
        if (!$reset_schedule && $advance_schedule) {
            // first get the schedule for the existing recurring contribution(s)
            $recurring_contributions = ContributionRecur::get(FALSE)
                ->addWhere('is_test', 'IS NOT NULL')
                ->addWhere('payment_token_id', '=', $payment_token_id)
                ->addWhere('contribution_status_id:name', 'IN', self::PROCESS_STATUS_NAMES)
                ->addSelect('id', 'next_sched_contribution_date', 'frequency_interval', 'frequency_unit')
                ->execute()
                ->getArrayCopy();

            // loop through each recurring contribution and set the date in the future
            foreach($recurring_contributions as &$recurring_contribution) {
                $next_scheduled = new DateTime($recurring_contribution['next_sched_contribution_date']);
                while ($next_scheduled < $today) {
                    $next_scheduled->modify("+{$recurring_contribution['frequency_interval']} {$recurring_contribution['frequency_unit']}");
                }
                $recurring_contribution['next_sched_contribution_date'] = $next_scheduled->format('Y-m-d H:i:s');

                // Don't send through the same frequency interval and unit again
                unset($recurring_contribution['frequency_interval'], $recurring_contribution['frequency_unit']);
            }

            if (!empty($recurring_contributions)) {
                // finally, save all the found recurring contributions
                ContributionRecur::save(FALSE)
                    ->setDefaults([
                        'contribution_status_id:name' => 'In Progress',
                        'failure_count' => 0,
                        'failure_retry_date' => null,
                    ])
                    ->setRecords($recurring_contributions)
                    ->execute();
            }
        }
        // When not configured to advance the scheduled date
        else {
            // Update every matching recurring contribution with in progress status and clear failure parameters
            $update_action = ContributionRecur::update(FALSE)
                ->addWhere('is_test', 'IS NOT NULL')
                ->addWhere('payment_token_id', '=', $payment_token_id)
                ->addWhere('contribution_status_id:name', 'IN', self::PROCESS_STATUS_NAMES)
                ->addValue('failure_count', '0')
                ->addValue('contribution_status_id:name', 'In Progress')
                ->addValue('failure_retry_date', null);

            if($reset_schedule) {
                $update_action->addValue('next_sched_contribution_date', $today->format('Y-m-d H:i:s'));
            }

            $update_action->execute();
        }
    }

    public function getConfigurationSpecification()
    {
        return new SpecificationBag([
            new Specification('advance_next_scheduled_date_default', 'Boolean', E::ts('Advance the next scheduled date'), true, true, null, null, false, E::ts('Advance the next scheduled date of the recurring contribution to after the date of the status reset, according to its schedule.')),
            new Specification('reset_next_scheduled_date_default', 'Boolean', E::ts('Restart schedule from today'), true, 0, null, null, false, E::ts('Make the next schedule date of the recurring contribution today and advance the schedule accordingly when next processed.')),
        ]);
    }

    public function getParameterSpecification()
    {
        return new SpecificationBag([
            new Specification('payment_token_id', 'Integer', E::ts('Payment Token Id'), true),
            new Specification('advance_next_scheduled_date', 'Boolean', E::ts('Advance the next scheduled date'), false),
            new Specification('reset_next_scheduled_date', 'Boolean', E::ts('Restart schedule from today'), false),
        ]);
    }

    public function getOutputSpecification()
    {
        return parent::getOutputSpecification();
    }
}