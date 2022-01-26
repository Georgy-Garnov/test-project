<?php

namespace App\Common;

use Doctrine\DBAL\Exception;
use LogicException;

class Currency {
  private string $currency_id;

  private static array $instances = [];

  /**
   * Get currency with check if valid
   *
   * @param string $currency_id
   * @param bool   $needCheck check if currency exists in DB
   * @return Currency
   * @throws Exception
   */
  public static function get(string $currency_id, bool $needCheck = TRUE): Currency {
    if (strlen($currency_id) !== 3) {
      throw new LogicException('Invalid currency id');
    }
    if (isset(static::$instances[$currency_id])) {
      return static::$instances[$currency_id];
    }
    $currency = new Currency($currency_id);
    static::$instances[$currency_id] = $currency;
    return $currency;
  }

  /**
   * currency_id
   * @return string
   */
  public function currency_id(): string {
    return $this->currency_id;
  }

  /**
   * Constructor is private no direct instantiation
   * @param string $currency_id
   * @param bool   $needCheck check if currency exists in DB
   * @throws Exception
   * @throws LogicException
   */
  private function __construct(string $currency_id, bool $needCheck = TRUE) {
    $this->currency_id = $currency_id;
    if ($needCheck) {
      $this->getCurrencyFromDB();
    }
  }

  /**
   * Check if currency is registered in DB
   * @throws Exception
   * @throws LogicException
   */
  private function getCurrencyFromDB(): void {
    $q = '
      SELECT cur.currency_id
      FROM currency as cur
      WHERE cur.currency_id = :currency_id
    ';
    $connection = DBConnection::getConnection();
    $result = $connection->prepare($q)->executeQuery(['currency_id' => $this->currency_id]);
    if ($result->rowCount() < 1) {
      throw new LogicException('Currency not found in DB: '. $this->currency_id);
    }
  }
}
