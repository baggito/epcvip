<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductController extends AbstractController
{
    /**
     * Create a new Product
     */
    public function create()
    {
        return new JsonResponse([__FUNCTION__]);
    }

    /**
     * Retrieve an existing Product
     */
    public function read()
    {
        return new JsonResponse([__FUNCTION__]);
    }

    /**
     * Update an existing Product
     */
    public function update()
    {
        return new JsonResponse([__FUNCTION__]);
    }

    /**
     * Delete a Product
     */
    public function delete()
    {
        return new JsonResponse([__FUNCTION__]);
    }
}
