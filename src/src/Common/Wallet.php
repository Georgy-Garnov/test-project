<?php

namespace App\Common;

use Doctrine\DBAL\Exception;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use LogicException;

class Wallet implements \JsonSerializable {

  public Currency $currency;

  /**
   * @param int    $user_id
   * @param int    $wallet_id
   * @param int    $currency_id
   * @param string $amount
   * @throws Exception
   */
  private function __construct(
    public int $user_id,
    public int $wallet_id,
    public int $currency_id,
    public string $amount
  ) {
    $this->currency = Currency::get($this->currency_id, FALSE);
  }

  /**
   * Return wallet by user id
   * @param int $user_id
   * @return Wallet
   * @throws Exception
   */
  public static function getByUserId(int $user_id): Wallet {
    return static::loadFromDb('user_id', $user_id);
  }

  /**
   * Return wallet by wallet id
   * @param int $wallet_id
   * @return Wallet
   * @throws Exception
   */
  public static function getByWalletId(int $wallet_id): Wallet {
    return static::loadFromDb('wallet_id', $wallet_id);
  }

  /**
   * @param string $key
   * @param int    $param
   * @return Wallet
   * @throws Exception
   */
  private static function loadFromDb(string $key, int $param): Wallet {
    $q = "
      SELECT
        w.wallet_id,
        w.user_id,
        w.currency_id,
        w.amount
      FROM wallet AS w
      WHERE w.$key = :param
    ";
    $connection = DBConnection::getConnection();
    $result = $connection->prepare($q)->executeQuery(['param' => $param]);
    if ($result->rowCount() === 0) {
      throw new LogicException("Wallet for $key not found");
    }
    $args = $result->fetchAssociative();
    return new Wallet(...$args);
  }

  /**
   * Refresh amount from DB
   *
   * @return string
   * @throws Exception
   */
  public function refreshAmount(): string {
    $wallet = static::getByUserId($this->user_id);
    return $this->amount = $wallet->amount;
  }

  /**
   * Change amount in wallet
   *
   * @param string $changeAmount Amount to chang (+ add - subtract)
   * @throws Exception
   */
  private function changeAmountInternal(string $changeAmount): void {
    if (!is_numeric($changeAmount)) {
      throw new LogicException('Invalid amount fow wallet amount change not numeric');
    }
    $q = '
      UPDATE wallet
      SET amount = amount + :amount
      WHERE wallet_id = :wallet_id
    ';
    $connection = DBConnection::getConnection();
    $connection->prepare($q)->executeQuery([
      'amount' => $changeAmount,
      'wallet_id' => $this->wallet_id,
    ]);
    $this->refreshAmount();
  }

  /**
   * Perform operation on wallet balance
   *
   * @param ETransactionType   $type
   * @param Currency           $currency
   * @param string             $amount
   * @param ETransactionReason $reason
   * @throws Exception
   */
  public function makeTransaction(ETransactionType $type, Currency $currency, string $amount, ETransactionReason $reason): void {
    $connection = DBConnection::getConnection();
    $connection->beginTransaction();
    try {
      $transaction = new Transaction($this->wallet_id, $type, $currency, $this->currency, $amount, $reason);
      $transaction->save();
      $amountWallet = $transaction->amountWallet;
      if ($type === ETransactionType::Credit) {
        $amountWallet = bcmul($amountWallet, -1, 2);
      }
      $this->changeAmountInternal($amountWallet);
      $connection->commit();
    } catch (\Exception $e) {
      $connection->rollBack();
      throw $e;
    }
  }


  #[ArrayShape(['wallet_id' => "int", 'user_id' => "int", 'currency_id' => "string", 'amount' => "string"])]
  #[Pure]
  public function jsonSerialize(): array {
    return [
      'wallet_id' => $this->wallet_id,
      'user_id' => $this->user_id,
      'currency_id' => $this->currency->currency_id(),
      'amount' => $this->amount,
    ];
  }
}
