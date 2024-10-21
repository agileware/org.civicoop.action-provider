<?php

namespace Civi\ActionProvider\Exception;

class ExecutionException extends \Exception {

  protected $rollbackDBTransaction = true;

  /**
   * Construct the exception. Note: The message is NOT binary safe.
   *
   * @link https://php.net/manual/en/exception.construct.php
   *
   * @param string $message [optional] The Exception message to throw.
   * @param int $code [optional] The Exception code.
   * @param null|\Throwable $previous [optional] The previous throwable used for
   *   the exception chaining.
   * @param bool $rollbackDBTransaction
   *   Rollback the database transaction. Default to true.
   */
  public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = NULL, bool $rollbackDBTransaction = true) {
    parent::__construct($message, $code, $previous);
    $this->rollbackDBTransaction = $rollbackDBTransaction;
  }

  /**
   * @return bool
   */
  public function rollbackDBTransaction(): bool {
    return $this->rollbackDBTransaction;
  }

}
