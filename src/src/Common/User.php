<?php

namespace App\Common;

use Doctrine\DBAL\Exception;

class User {

  public int $user_id;
  private Wallet $wallet;

  /**
   * Загрузка пользователя из БД
   *
   * @param $user_id
   * @return User
   */
  public static function getUser($user_id): User {

  }

  /**
   * Get user wallet
   * @return Wallet
   * @throws Exception
   */
  public function getWallet(): Wallet {
    return $this->wallet ?? ($this->wallet = Wallet::getByUserId($this->user_id));
  }
}
