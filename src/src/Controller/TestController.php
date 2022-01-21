<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Connection;


class TestController extends AbstractController {

  /** @var Connection */
  private $connection;

  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * @Route("/api/get_balance/{id}", methods={"GET"})
   * @param int $id
   * @return JsonResponse
   */
  public function getBalance(int $id) {
    $data = $this->readBalance($id);
    if ($data === FALSE) {
      $data = [
        'status' => '404',
        'error' => "Wallet not found",
      ];
    }
    return $this->response($data);
  }

  /**
   * @Route("/api/add_transaction", methods={"POST"})
   * @return JsonResponse
   */
  public function addTransaction(Request $request) {
    $request = $this->transformJsonBody($request);
    var_export($request->get('foewj'));
    $data = [
      'status' => '404',
      'errors' => "Wallet not found",
    ];
    return $this->response($data);
  }

  private function readBalance(int $wallet_id) {
    $q = '
      SELECT
        wallet_id,
        currency_id,
        user_id,
        amount
      FROM wallet as w
      WHERE wallet_id = :id
    ';
    return $this->connection->prepare($q)->executeQuery(['id' => $wallet_id])->fetchAssociative();
  }

  public function response($data, $status = 200, $headers = []) {
    return new JsonResponse($data, $status, $headers);
  }

  protected function transformJsonBody(\Symfony\Component\HttpFoundation\Request $request)
  {
    $data = json_decode($request->getContent(), TRUE);
    if ($data === NULL) {
      return $request;
    }
    $request->request->replace($data);
    return $request;
  }

}
