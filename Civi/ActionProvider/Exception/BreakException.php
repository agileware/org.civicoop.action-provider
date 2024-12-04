<?php

namespace Civi\ActionProvider\Exception;

/**
 * @class
 *
 * "Exception" class for flow control. Prevents further execution of Form Processor loops.
 */
class BreakException extends \Exception {

  /**
   * Whether to rollback the current database transaction.
   *
   * @return false
   */
  public function rollbackDBTransaction() {
    return FALSE;
  }
}
