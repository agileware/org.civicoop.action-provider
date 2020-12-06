<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider;

use Civi\ActionProvider\Event\ConfigContainerBuilderEvent;
use Civi\ActionProvider\Utils\CustomField;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

class ConfigContainer {

  /**
   * @var \Symfony\Component\DependencyInjection\Container
   */
  public static $configContainer;

  private function __construct() {
  }

  /**
   * @return \Civi\ActionProvider\Config
   */
  public static function getInstance() {
    if (!self::$configContainer) {
      $file = self::getCacheFile();
      if (!file_exists($file)) {
        $containerBuilder = self::createContainer();
        $containerBuilder->compile();
        $dumper = new PhpDumper($containerBuilder);
        file_put_contents($file, $dumper->dump([
          'class' => 'CachedActionProviderConfigContainer',
          'base_class' => '\Civi\ActionProvider\Config',
        ]));
      }
      require_once $file;
      self::$configContainer = new \CachedActionProviderConfigContainer();
    }
    return self::$configContainer;
  }

  /**
   * Clear the cache.
   */
  public static function clearCache() {
    $file = self::getCacheFile();
    if (file_exists($file)) {
      unlink($file);
    }
  }

  /**
   * Clears the cached configuration file ony when custom field or custom group has been saved.
   *
   * @param $op
   * @param $objectName
   * @param $objectId
   * @param $objectRef
   */
  public static function postHook($op, $objectName, $id, &$objectRef) {
    $clearCacheObjects = ['CustomGroup', 'CustomField'];
    if (in_array($objectName, $clearCacheObjects)) {
      self::clearCache();
    }
  }

  /**
   * The name of the cache file.
   *
   * @return string
   */
  public static function getCacheFile() {
    $envId = \CRM_Core_Config_Runtime::getId();
    return \Civi::paths()->getPath("[civicrm.compile]/CachedActionProviderConfigContainer.{$envId}.php");
  }

  /**
   * Create the containerBuilder
   *
   * @return \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  protected static function createContainer() {
    $containerBuilder = new ContainerBuilder();

    Config::buildConfigContainer($containerBuilder);

    // Dipsatch an symfony event so that extensions could listen to this event
    // and hook int the building of the config container.
    $event = new ConfigContainerBuilderEvent($containerBuilder);
    \Civi::dispatcher()->dispatch(ConfigContainerBuilderEvent::EVENT_NAME, $event);
    return $containerBuilder;
  }

}
