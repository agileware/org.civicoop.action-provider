<?php
declare(strict_types = 1);

namespace Civi\ActionProvider\Action\Contact;

use Civi\ActionProvider\Action\AbstractActionTestCase;
use Civi\ActionProvider\Exception\InvalidParameterException;
use Civi\ActionProvider\Parameter\ParameterBag;
use Civi\Api4\Contact;
use Civi\Api4\Email;

/**
 * @covers \Civi\ActionProvider\Action\Contact\EmailResetOnHold
 *
 * @group headless
 */
final class EmailResetOnHoldTest extends AbstractActionTestCase {

  protected function getActionName(): string {
    return 'EmailResetOnHold';
  }

  public function test(): void {
    static::assertInstanceOf(EmailResetOnHold::class, $this->action);

    $e = NULL;
    try {
      $parameters = new ParameterBag();
      $conditionParameters = new ParameterBag();
      $invalidConditionOutput = new ParameterBag();
      $this->action->execute($parameters, $conditionParameters, $invalidConditionOutput);
    } catch (InvalidParameterException $e) {
      // @ignoreException
    }
    static::assertNotNull($e);

    /** @var @param array{id: int, ...} $contact */
    $contact1 = Contact::create(FALSE)
      ->setValues([
        'contact_type' => 'Individual',
        'first_name' => 'Some',
        'last_name' => 'Individual',
      ])
      ->execute()
      ->single();

    /** @var array{id: int, ...} $email1_1 */
    $email1_1 = Email::create(FALSE)
      ->setValues([
        'contact_id' => $contact1['id'],
        'email' => 'email1@example.org',
        'on_hold' => 1,
      ])
      ->execute()
      ->single();
    /** @var array{id: int, ...} $email1_2 */
    $email1_2 = Email::create(FALSE)
      ->setValues([
        'contact_id' => $contact1['id'],
        'email' => 'email2@example.org',
        'on_hold' => 1,
      ])
      ->execute()
      ->single();

    /** @var @param array{id: int, ...} $contact */
    $contact2 = Contact::create(FALSE)
      ->setValues([
        'contact_type' => 'Individual',
        'first_name' => 'Other',
        'last_name' => 'Individual',
      ])
      ->execute()
      ->single();

    /** @var array{id: int, ...} $email2_1 */
    $email2_1 = Email::create(FALSE)
      ->setValues([
        'contact_id' => $contact2['id'],
        'email' => 'email1@example.org',
        'on_hold' => 1,
      ])
      ->execute()
      ->single();

    $parameters->setParameter('contact_id', $contact1['id']);
    $parameters->setParameter('email', 'email1@example.org');
    // on_hold flag of the first email of contact1 is reset.
    $this->action->execute($parameters, $conditionParameters, $invalidConditionOutput);

    $email1_1GetOnHold = Email::get(FALSE)->addSelect('on_hold')->addWhere('id', '=', $email1_1['id']);
    $email1_2GetOnHold = Email::get(FALSE)->addSelect('on_hold')->addWhere('id', '=', $email1_2['id']);
    $email2_1GetOnHold = Email::get(FALSE)->addSelect('on_hold')->addWhere('id', '=', $email2_1['id']);
    static::assertSame(0, $email1_1GetOnHold->execute()->single()['on_hold']);
    static::assertSame(1, $email1_2GetOnHold->execute()->single()['on_hold']);
    static::assertSame(1, $email2_1GetOnHold->execute()->single()['on_hold']);

    $parameters = new ParameterBag();
    $parameters->setParameter('contact_id', $contact1['id']);
    // on_hold flag of all emails of contact1 are reset.
    $this->action->execute($parameters, $conditionParameters, $invalidConditionOutput);
    static::assertSame(0, $email1_2GetOnHold->execute()->single()['on_hold']);
    static::assertSame(1, $email2_1GetOnHold->execute()->single()['on_hold']);
  }

}
