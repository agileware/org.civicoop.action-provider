<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Event;

use Civi\Core\Event\GenericHookEvent;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConfigContainerBuilderEvent extends GenericHookEvent {

  const EVENT_NAME = 'ActionProviderConfigContainerBuilderEvent';

  /**
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  public $containerBuilder;

  public function __construct(ContainerBuilder $containerBuilder) {
    $this->containerBuilder = $containerBuilder;
  }

}
