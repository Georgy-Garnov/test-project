<?php

namespace App\Command;

use App\Common\DBConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Common\Currency;

class RefreshRatesCommand extends Command {
  protected static $defaultName = 'app:refresh-rates';
  /** @var Connection */
  private $connection;
  /** @var ContainerInterface */
  private $httpClient;
  private $rateKey;

  public function __construct(Connection $connection, HttpClientInterface $client) {
    DBConnection::setConnection($connection);
    $this->connection = $connection;
    $this->httpClient = $client;
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
    $currency = new Currency();
    $currency->currency_id = 'RUB';
    $output->writeln(var_export($currency, TRUE));
    return Command::SUCCESS;
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

  public function fetchGitHubInformation(): array
  {
    $response = $this->httpClient->request(
      'GET',
      'https://api.github.com/repos/symfony/symfony-docs'
    );

    $statusCode = $response->getStatusCode();
    var_export($statusCode);
    // $statusCode = 200
    $contentType = $response->getHeaders()['content-type'][0];
    var_export($contentType);
    // $contentType = 'application/json'
    $content = $response->getContent();
    var_export($content);
    // $content = '{"id":521583, "name":"symfony-docs", ...}'
    $content = $response->toArray();
    var_export($content);
    // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

    return $content;
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
