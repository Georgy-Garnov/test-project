<?php

namespace App\Common;

class ExchangeRate {

  public $from;
  public $to;
  public $exchange_rate;
  public $created;

  /**
   * Фабричный метод для получения курса обмена по заданным параметрам
   *
   * @param string $from
   * @param string $to
   * @return ExchangeRate
   */
  public static function get(string $from, string $to): ExchangeRate {

  }

  /**
   * Перевод суммы из одной валюты в другую по курсу
   *
   * @param string $amount
   * @return string
   */
  public function convertAmount(string $amount): string {
    return bcmul($amount, $this->exchange_rate, 4);
  }
}
