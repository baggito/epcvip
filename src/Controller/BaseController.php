<?php

namespace App\Controller;

use App\Helper\LoggerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class BaseController extends AbstractController
{
    use LoggerTrait;

    /**
     * Helper method. Returns json formatted response.
     *
     * @param array $data
     * @param int $code
     * @param bool $noError
     * @return JsonResponse
     */
    protected function response(Array $data, int $code, bool $noError = true)
    {
        return new JsonResponse([
            'success' => $noError,
            'data'    => $data
        ], $code);
    }
}
