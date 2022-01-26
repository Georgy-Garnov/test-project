<?php

namespace App\Common;

use Doctrine\DBAL\Exception;
use LogicException;

class ExchangeRate {

  /**
   * Fabric for getting actual exchange rate for currency pair
   *
   * @param Currency $from
   * @param Currency $to
   * @return ExchangeRate
   * @throws Exception
   */
  public static function get(Currency $from, Currency $to): ExchangeRate {
    if ($from->currency_id() === $to->currency_id()) {
      return self::getDirectExchange($from);
    }
    return self::loadFromDB($from, $to);
  }

  /**
   * 1 to 1 Exchange rate - no conversation
   * @param Currency $currency
   * @return ExchangeRate
   */
  private static function getDirectExchange(Currency $currency): ExchangeRate {
    return new ExchangeRate($currency, $currency, '1', time());
  }

  /**
   * Load exchange rate from db
   *
   * @param Currency $from
   * @param Currency $to
   * @return ExchangeRate
   * @throws Exception
   */
  private static function loadFromDB(Currency $from, Currency $to): ExchangeRate {
    $q = '
      SELECT
        cr.created,
        cr.exchange_rate
      FROM currency_rate AS cr
      WHERE cr.currency_id_from = :from AND cr.currency_id_to = :to
      ORDER BY cr.rate_id DESC
      LIMIT 1
    ';
    $connection = DBConnection::getConnection();
    $result = $connection->prepare($q)->executeQuery([
      'from' => $from->currency_id(),
      'to' => $to->currency_id(),
    ]);
    if ($result->rowCount() === 0) {
      throw new LogicException('Exchange rate not found');
    }
    $data = $result->fetchAssociative();
    return new ExchangeRate($from, $to, $data['exchange_rate'], $data['created']);
  }

  private function __construct(
    public Currency $from,
    public Currency $to,
    public string $exchange_rate,
    public int $created
  ) {}

  /**
   * Convert amount from currency to currency
   *
   * @param string $amount
   * @return string
   */
  public function convertAmount(string $amount): string {
    if ($this->exchange_rate === '1') {
      return $amount;
    }
    // @fixme think about adding bank round algorithm here because of loss on conversation
    return bcmul($amount, $this->exchange_rate, 4);
  }
}
