<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class CustomerController extends AbstractController
{
    /**
     * Create a new Customer
     */
    public function create()
    {
        return new JsonResponse([__FUNCTION__]);
    }

    /**
     * Retrieve an existing Customer
     */
    public function read()
    {
        return new JsonResponse([__FUNCTION__]);
    }

    /**
     * Update an existing Customer
     */
    public function update()
    {
        return new JsonResponse([__FUNCTION__]);
    }

    /**
     * Delete a Customer
     */
    public function delete()
    {
        return new JsonResponse([__FUNCTION__]);
    }
}
