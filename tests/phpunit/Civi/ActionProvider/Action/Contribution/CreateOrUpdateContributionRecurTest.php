<?php
declare(strict_types = 1);

namespace Civi\ActionProvider\Action\Contribution;

use Civi\ActionProvider\Action\AbstractActionTestCase;
use Civi\ActionProvider\Parameter\ParameterBag;
use Civi\Api4\Contact;
use Civi\Api4\ContributionRecur;

/**
 * @covers \Civi\ActionProvider\Action\Contribution\CreateOrUpdateContributionRecur
 *
 * @group headless
 */
final class CreateOrUpdateContributionRecurTest extends AbstractActionTestCase {

  protected function getActionName(): string {
    return 'CreateOrUpdateContributionRecur';
  }

  public function test(): void {
    static::assertInstanceOf(CreateOrUpdateContributionRecur::class, $this->action);

    /** @var array{id: int, ...} $contact */
    $contact = Contact::create(FALSE)
      ->setValues([
        'contact_type' => 'Individual',
        'first_name' => 'Some',
        'last_name' => 'Individual',
      ])
      ->execute()
      ->single();

    $configuration = new ParameterBag();
    $configuration->setParameter('financial_type_id', 1);
    $configuration->setParameter('frequency_unit', 'month');
    $configuration->setParameter('frequency_interval', 1);
    $configuration->setParameter('currency', 'USD');
    $configuration->setParameter('is_test', '0');
    $configuration->setParameter('installments', '');
    $configuration->setParameter('contribution_status_id', 2);
    $this->action->setConfiguration($configuration);

    $parameters = new ParameterBag();
    $parameters->setParameter('contact_id', $contact['id']);
    $parameters->setParameter('amount', 25);
    $parameters->setParameter('trxn_id', 'TRXN-1');
    $parameters->setParameter('invoice_id', 'INV-1');
    // A mapped parameter overrides the configured value.
    $parameters->setParameter('frequency_interval', 3);
    $conditionParameters = new ParameterBag();
    $invalidConditionOutput = new ParameterBag();

    $output = $this->action->execute($parameters, $conditionParameters, $invalidConditionOutput);
    $id = $output->getParameter('id');
    static::assertNotEmpty($id);
    static::assertSame('USD', $output->getParameter('currency'));

    $recur = ContributionRecur::get(FALSE)
      ->addWhere('id', '=', $id)
      ->execute()
      ->single();
    static::assertSame($contact['id'], $recur['contact_id']);
    static::assertSame('TRXN-1', $recur['trxn_id']);
    static::assertSame('INV-1', $recur['invoice_id']);
    static::assertSame(3, $recur['frequency_interval']);
    static::assertSame('month', $recur['frequency_unit']);
    static::assertFalse($recur['is_test']);
    static::assertNull($recur['installments']);

    // Passing the id updates the same recurring contribution.
    $parameters = new ParameterBag();
    $parameters->setParameter('id', $id);
    $parameters->setParameter('amount', 30);
    $output = $this->action->execute($parameters, $conditionParameters, $invalidConditionOutput);
    static::assertSame($id, $output->getParameter('id'));

    $recur = ContributionRecur::get(FALSE)
      ->addWhere('id', '=', $id)
      ->execute()
      ->single();
    static::assertEquals(30, $recur['amount']);
    static::assertSame('TRXN-1', $recur['trxn_id']);
    static::assertSame(1, ContributionRecur::get(FALSE)->addWhere('contact_id', '=', $contact['id'])->execute()->count());
  }

}
