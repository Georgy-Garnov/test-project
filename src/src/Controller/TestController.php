<?php

namespace App\Controller;

use App\Common\Currency;
use App\Common\ETransactionReason;
use App\Common\ETransactionType;
use App\Common\User;
use App\Common\Wallet;
use Exception;
use JsonException;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Connection;
use App\Common\DBConnection;


class TestController extends AbstractController {

  public function __construct(Connection $connection) {
    DBConnection::setConnection($connection);
  }

  /**
   * @Route("/api/get_balance/{id}", methods={"GET"})
   * @param int $id
   * @return JsonResponse
   */
  public function getBalance(int $id): JsonResponse {
    try {
      $data = Wallet::getByWalletId($id);
    } catch (Exception $e) {
      $data = [
        'status' => '404',
        'error' => $e->getMessage(),
      ];
    }
    return $this->response($data);
  }

  /**
   *
   * request body json
   * [
   * user_id: int
   * type: string-enum (debit|credit)
   * reason: string-enum (stock|refund)
   * amount: decimal
   * ]
   * @Route("/api/add_transaction", methods={"POST"})
   * @param Request $request
   * @return JsonResponse
   */
  public function addTransaction(Request $request): JsonResponse {
    try {
      $request = $this->transformJsonBody($request);
      // @fixme need to select validation package for symfony rewrite with validation package
      $user_id = $request->get('user_id');
      if (!is_numeric($user_id)) {
        throw new LogicException('Invalid user_id');
      }
      $user = User::getUser($user_id);
      $type = ETransactionType::tryFrom($request->get('type'));
      if ($type === NULL) {
        throw new LogicException('Invalid transaction type');
      }
      $reason = ETransactionReason::tryFrom($request->get('reason'));
      if ($reason === NULL) {
        throw new LogicException('Invalid transaction reason');
      }
      $amount = $request->get('amount');
      if (!is_numeric($amount)) {
        throw new LogicException('Invalid amount');
      }
      $currency_id = $request->get('currency_id');
      $currency = Currency::get($currency_id);

      $wallet = $user->getWallet();
      $wallet->makeTransaction($type, $currency, $amount, $reason);
      $data = $wallet;
    } catch (Exception $e) {
      $data = [
        'status' => '404',
        'errors' => $e->getMessage(),
      ];
    }
    return $this->response($data);
  }

  public function response($data, $status = 200, $headers = []): JsonResponse {
    return new JsonResponse($data, $status, $headers);
  }

  /**
   * @throws JsonException
   */
  protected function transformJsonBody(Request $request): Request {
    $data = json_decode($request->getContent(), TRUE, 512, JSON_THROW_ON_ERROR);
    if ($data === NULL) {
      return $request;
    }
    $request->request->replace($data);
    return $request;
  }

}
