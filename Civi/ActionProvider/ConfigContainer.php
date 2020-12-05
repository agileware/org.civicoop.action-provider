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
   * @return \Symfony\Component\DependencyInjection\Container
   */
  public static function getInstance() {
    if (!self::$configContainer) {
      $file = self::getCacheFile();
      $containerConfigCache = new ConfigCache($file, false);
      if (!$containerConfigCache->isFresh()) {
        $containerBuilder = self::createContainer();
        $containerBuilder->compile();
        $dumper = new PhpDumper($containerBuilder);
        $containerConfigCache->write(
          $dumper->dump(['class' => 'CachedActionProviderConfigContainer']),
          $containerBuilder->getResources()
        );
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
    $metaFile = $file.'.meta';
    if (file_exists($file)) {
      unlink($file);
    }
    if (file_exists($metaFile)) {
      unlink($metaFile);
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
    return \Civi::paths()->getPath("[civicrm.compile]/CachedActionProviderConfigContainer.php");
  }

  /**
   * Create the containerBuilder
   *
   * @return \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  protected static function createContainer() {
    $containerBuilder = new ContainerBuilder();

    CustomField::buildConfigContainer($containerBuilder);

    // Dipsatch an symfony event so that extensions could listen to this event
    // and hook int the building of the config container.
    $event = new ConfigContainerBuilderEvent($containerBuilder);
    \Civi::dispatcher()->dispatch(ConfigContainerBuilderEvent::EVENT_NAME, $event);
    return $containerBuilder;
  }

}
