<?php
declare(strict_types = 1);

namespace Civi\ActionProvider\Action;

use Civi\Test;
use Civi\Test\CiviEnvBuilder;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractActionTestCase extends TestCase implements HeadlessInterface, TransactionalInterface {

  protected AbstractAction $action;

  public function setUpHeadless(): CiviEnvBuilder {
    return Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  protected function setup(): void {
    parent::setup();
    /** @var \Civi\ActionProvider\Container $actionProviderContainer */
    $actionProviderContainer = \Civi::service('action_provider');
    $actionProvider = $actionProviderContainer->getDefaultProvider();
    $this->action = $actionProvider->getActionByName($this->getActionName());
  }

  abstract protected function getActionName(): string;

}
