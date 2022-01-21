<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220120200000 extends AbstractMigration {

  public function getDescription(): string {
    return '';
  }

  public function up(Schema $schema): void {
    $this->addSql('
      CREATE TABLE IF NOT EXISTS user(
        user_id INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
        PRIMARY KEY(user_id)
      )
    ');

    $this->addSql("
      CREATE TABLE IF NOT EXISTS currency(
        currency_id CHAR(3) NOT NULL COMMENT 'ISO4217 ALPHA3 currency code',
        PRIMARY KEY(currency_id)
      )
    ");

    $this->addSql("
      CREATE TABLE IF NOT EXISTS currency_rate(
        rate_id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
        currency_id_from CHAR(3) NOT NULL COMMENT 'ISO4217 ALPHA3 currency code',
        currency_id_to CHAR(3) NOT NULL COMMENT 'ISO4217 ALPHA3 currency code',
        exchange_rate DECIMAL(16, 4) NOT NULL COMMENT 'Курс обмена',
        created INT(10) UNSIGNED NOT NULL COMMENT 'Дата внесения',
        PRIMARY KEY(rate_id),
        CONSTRAINT fk_currency_rate_currency_currency_id
          FOREIGN KEY (currency_id_from) REFERENCES currency (currency_id),
        CONSTRAINT fk_currency_rate_currency_currency_id2
          FOREIGN KEY (currency_id_to) REFERENCES currency (currency_id)
      )
    ");

    $this->addSql("
      CREATE TABLE IF NOT EXISTS wallet(
        wallet_id INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
        user_id INT(10) UNSIGNED NOT NULL,
        currency_id CHAR(3) NOT NULL COMMENT 'ISO4217 ALPHA3 currency code',
        amount DECIMAL(16, 2) NOT NULL,
        PRIMARY KEY(wallet_id),
        CONSTRAINT fk_wallet_user_user_id
          FOREIGN KEY (user_id) REFERENCES user (user_id),
        CONSTRAINT fk_wallet_currency_currency_id
          FOREIGN KEY (currency_id) REFERENCES currency (currency_id)
      )
    ");

    $this->addSql('
      ALTER TABLE wallet
      ADD UNIQUE INDEX uk_wallet_user_id (user_id)
    ');

    $this->addSql("
      CREATE TABLE IF NOT EXISTS wallet_transaction(
        transaction_id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
        wallet_id INT(10) UNSIGNED NOT NULL,
        transaction_type ENUM('debit', 'credit') NOT NULL COMMENT 'Тип транзакции',
        currency_id CHAR(3) NOT NULL COMMENT 'Валюта транзакции',
        amount_transaction DECIMAL(16, 2) NOT NULL COMMENT 'Сумма в валюте транзакции',
        rate DECIMAL(16, 4) NOT NULL COMMENT 'Курс обмена в валюту кошелька',
        amount_wallet DECIMAL(16, 2) NOT NULL COMMENT 'Сумма в валюте кошелька',
        reason ENUM('stock', 'refund') NOT NULL COMMENT 'Причина изменения счета',
        created INT(10) UNSIGNED NOT NULL COMMENT 'Дата внесения',
        PRIMARY KEY(transaction_id),
        CONSTRAINT fk_wallet_transaction_currency_currency_id
          FOREIGN KEY (currency_id) REFERENCES currency (currency_id),
        CONSTRAINT fk_wallet_transaction_wallet_wallet_id
          FOREIGN KEY (wallet_id) REFERENCES wallet (wallet_id)
      )
    ");

  }

  public function down(Schema $schema): void {
    $this->addSql('DROP TABLE IF EXISTS wallet_transaction');

    $this->addSql('DROP TABLE IF EXISTS wallet');

    $this->addSql('DROP TABLE IF EXISTS currency_rate');

    $this->addSql('DROP TABLE IF EXISTS currency');

    $this->addSql('DROP TABLE IF EXISTS user');
  }
}
