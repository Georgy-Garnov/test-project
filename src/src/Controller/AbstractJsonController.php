<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractJsonController extends AbstractController {

  protected function response($data, $status = 200, $headers = []): JsonResponse {
    return new JsonResponse($data, $status, $headers);
  }

  /**
   * @throws \JsonException
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
