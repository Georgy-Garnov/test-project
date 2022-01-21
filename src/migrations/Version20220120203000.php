<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220120203000 extends AbstractMigration {

  public function getDescription(): string {
    return '';
  }

  public function up(Schema $schema): void {
    $this->addSql('
      INSERT INTO user (user_id) VALUES
      (1), (2)
    ');

    $this->addSql("
      INSERT INTO currency (currency_id) VALUES
      ('RUB'), ('USD')
    ");

    $this->addSql("
      INSERT INTO wallet (wallet_id, user_id, currency_id, amount) VALUES
      (1, 1, 'USD', 0), (2, 2, 'RUB', 0)
    ");

    $this->addSql("
      INSERT INTO currency_rate (currency_id_from, currency_id_to, exchange_rate, created) VALUES
      ('RUB', 'USD', 0.0156, UNIX_TIMESTAMP()), ('USD', 'RUB', 64.1824, UNIX_TIMESTAMP())
    ");

  }

  public function down(Schema $schema): void {
    $this->addSql('SET FOREIGN_KEY_CHECKS = 0');

    $this->addSql('TRUNCATE TABLE wallet_transaction');

    $this->addSql('TRUNCATE TABLE wallet');

    $this->addSql('TRUNCATE TABLE currency_rate');

    $this->addSql('TRUNCATE TABLE currency');

    $this->addSql('TRUNCATE TABLE user');

    $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
  }
}
