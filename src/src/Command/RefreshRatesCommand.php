<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RefreshRatesCommand extends Command {
  protected static $defaultName = 'app:refresh-rates';
  /** @var Connection */
  private $connection;
  /** @var ContainerInterface */
  private $container;
  private $rateKey;

  public function __construct(Connection $connection, ContainerInterface $container) {
    $this->connection = $connection;
    parent::__construct();
  }

  /**
   * Конфигурирование команды
   */
  protected function configure(): void
  {
    $this
      ->setHelp('Refresh currency rates')
      ->setDescription('Refresh currency rates');
  }

  /**
   * Выполнение команды
   *
   * @param InputInterface  $input
   * @param OutputInterface $output
   * @return int
   * @throws \Doctrine\DBAL\Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    try {
      $pairs = $this->getRatePairs();
      $url = $this->formatRequestUrl($pairs);

      var_export($pairs);
      var_export($url);
      $output->writeln('Проверка!');
      return Command::SUCCESS;
    } catch (\Exception $e) {
      $output->writeln('Ошибка получения курсов валют');
      return Command::FAILURE;
    }

    //return Command::INVALID;
  }

  private function getExchangeRates(array $pairs): array {

  }

  private function formatRequestUrl(array $pairs): string {
    $pairs = implode(',', $pairs);
    return "https://currate.ru/api/?get=rates&pairs=$pairs&key={$this->rateKey}";
  }

  private function formatPairString(array $pair): string {
    return $pair['currency_from'].$pair['currency_to'];
  }

  /**
   * Получение списка валютных пар
   *
   * @return array
   * @throws \Doctrine\DBAL\Exception
   */
  private function getRatePairs():array {
    $q = '
      SELECT
        CONCAT(c1.currency_id, c2.currency_id) AS pair
      FROM currency AS c1
      INNER JOIN currency AS c2 ON c1.currency_id != c2.currency_id
    ';
    return $this->connection->prepare($q)->executeQuery()->fetchFirstColumn();
  }
}
