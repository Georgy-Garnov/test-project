<?php

namespace App\Common;

use Doctrine\DBAL\Exception;
use LogicException;

class Transaction {
  public int $transaction_id;
  public ExchangeRate $rate;
  public string $amountWallet;
  public int $created;

  /**
   * @param int                $wallet_id
   * @param ETransactionType   $transactionType
   * @param Currency           $currency
   * @param Currency           $currencyWallet
   * @param string             $amount
   * @param ETransactionReason $reason
   * @throws Exception
   */
  public function __construct(
    public int $wallet_id,
    public ETransactionType $transactionType,
    public Currency $currency,
    public Currency $currencyWallet,
    public string $amount,
    public ETransactionReason $reason
  ) {
    $this->created = time();
    $this->rate = ExchangeRate::get($this->currency, $this->currencyWallet);
    $this->amountWallet = $this->rate->convertAmount($this->amount);
  }

  /**
   * @throws Exception
   */
  public function save(): void {
    $q = '
      INSERT INTO wallet_transaction
      (wallet_id, transaction_type, currency_id, amount_transaction, rate, amount_wallet, reason, created)
      VALUES
      (:wallet_id, :transaction_type, :currency_id, :amount_transaction, :rate, :amount_wallet, :reason, :created)
    ';
    $connection = DBConnection::getConnection();
    $result = $connection->prepare($q)->executeQuery(
      [
        'wallet_id' => $this->wallet_id,
        'transaction_type' => $this->transactionType->value,
        'currency_id' => $this->currency->currency_id(),
        'amount_transaction' => $this->amount,
        'rate' => $this->rate->exchange_rate,
        'amount_wallet' => $this->amountWallet,
        'reason' => $this->reason->value,
        'created' => $this->created,
      ]
    );
    if ($result->rowCount() === 0) {
      throw new LogicException('Error saving transaction in DB');
    }
    $this->transaction_id = $connection->lastInsertId();
  }
}
