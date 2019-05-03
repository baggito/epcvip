<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Product;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Faker\Factory;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CustomerController extends BaseController
{

    /**
     * Returns all customers
     *
     * @param EntityManagerInterface $em
     * @return JsonResponse
     *
     * @Route("/api/customers", methods={"GET"}, name="customers_index")
     */
    public function index(EntityManagerInterface $em)
    {
        // Get all customers
        $customers = Customer::notDeletedCustomers($em);

        // If there is no any customer
        if (! count($customers)) {
            return $this->response([], Response::HTTP_NO_CONTENT);
        }

        // Data for response
        $data = [];

        // Generate user friendly response
        foreach ($customers as $customer) {
            $data[] = $this->customerToArray($customer);
        }

        // Call to log the data
//        $this->logInfo('/customers', $data);

        return $this->response($data, Response::HTTP_OK);
    }

    /**
     * Extract Customer object into array
     *
     * @param Customer $customer
     * @return array
     */
    public function customerToArray(?Customer $customer)
    {
        if ($customer) {
            // Generate user friendly response
            $data = [
                'id'         => $customer->getId(),
                'first_name' => $customer->getFirstName(),
                'last_name'  => $customer->getLastName(),
                'status'     => $customer->getStatus(),
                'birth'      => $customer->getDateOfBirth(),
                'created_at' => $customer->getCreatedAt()
            ];
        } else {
            $data = [];
        }

        return $data;
    }
    /**
     * Generate unique uuid
     *
     * @param EntityManagerInterface $em
     * @return UuidInterface|string
     */
    private function generateUuid(EntityManagerInterface $em)
    {
        do {
            // Generate uuid
            try {
                $uuid = Uuid::uuid4();
            } catch (Exception $e) {
                $uuid = Factory::create()->uuid;
            }

            // Check that uuid is unique
            $customer = $em->getRepository(Customer::class)->findBy(['uuid' => $uuid]);
        } while ($customer);

        return $uuid;
    }

    /**
     * Validate request data
     *
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return array
     */
    private function validateRequest(Request $request, ValidatorInterface $validator)
    {
        // Validate incoming request
        $constraints = new Assert\Collection([
            'first_name' => [
                new Assert\NotBlank,
                new Assert\Length(['min' => 2])
            ],
            'last_name' => [
                new Assert\NotBlank,
                new Assert\Length(['min' => 2])
            ],
            'status' => [
                // Merge empty value with customer statuses, that will allow user to send empty status,
                // by default if status is empty we set it 'new'
                new Assert\Choice(array_merge(Customer::$customer_statuses, ['']))
            ],
            'date_of_birth' => [
                new Assert\NotBlank,
            ],
        ]);

        $violations = $validator->validate($request->request->all(), $constraints);

        $accessor = PropertyAccess::createPropertyAccessor();

        $errorMessages = [];

        if ($violations->count()) {

            foreach ($violations as $violation) {
                $accessor->setValue($errorMessages,
                    $violation->getPropertyPath(),
                    $violation->getMessage());
            }

            return ['error' => $errorMessages];
        }

        return ['success' => true];
    }

    /**
     * Create a new Customer
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return JsonResponse
     *
     * @Route("/api/customers", methods={"POST"}, name="customers_create")
     */
    public function create(EntityManagerInterface $em, Request $request, ValidatorInterface $validator)
    {
        // Validate request
        $validation = $this->validateRequest($request, $validator);

        // Return validation error messages if validation failed
        if (isset($validation['error'])) {
            return $this->response($validation['error'], Response::HTTP_BAD_REQUEST, false);
        }

        $customer = new Customer();
        $customer->setUuid($this->generateUuid($em));
        $customer->setFirstName($request->request->get('first_name'));
        $customer->setLastName($request->request->get('last_name'));
        $customer->setDateOfBirth($request->request->get('date_of_birth'));
        $customer->setStatus($request->request->get('status'));

        $em->persist($customer);
        $em->flush();

        // Get just created customer by id
        $customer = $em->getRepository(Customer::class)->findOneBy(['id' => $customer->getId()]);

        return $this->response($this->customerToArray($customer), Response::HTTP_OK);
    }

    /**
     * Retrieve an existing Customer
     *
     * @param int $id
     * @param EntityManagerInterface $em
     * @return JsonResponse
     *
     * @Route("/api/customers/{id}", methods={"GET"}, name="customers_read")
     */
    public function read(int $id, EntityManagerInterface $em)
    {
        // Get customer by id
        $customer = Customer::notDeletedCustomerById($em, $id);

        // If there is no customer with given id
        if (! $customer) {
           return $this->response([], Response::HTTP_NOT_FOUND, false);
        }

        return $this->response($this->customerToArray($customer), Response::HTTP_OK);
    }

    /**
     * Update an existing Customer
     *
     * @param int $id
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return JsonResponse
     *
     * @Route("/api/customers/{id}", methods={"POST"}, name="customers_update")
     */
    public function update(int $id, EntityManagerInterface $em, Request $request, ValidatorInterface $validator)
    {

        // Get customer by id
        $customer = Customer::notDeletedCustomerById($em, $id);

        // If there is no customer with given id
        if (! $customer) {
            return $this->response([], Response::HTTP_NOT_FOUND, false);
        }

        // Validate request
        $validation = $this->validateRequest($request, $validator);

        // Return validation error messages if validation failed
        if (isset($validation['error'])) {
            return $this->response($validation['error'], Response::HTTP_BAD_REQUEST, false);
        }

        $customer->setUuid($this->generateUuid($em));
        $customer->setFirstName($request->request->get('first_name'));
        $customer->setLastName($request->request->get('last_name'));
        $customer->setDateOfBirth($request->request->get('date_of_birth'));
        $customer->setStatus($request->request->get('status'));
        $em->flush();

        // Get just created customer by id
        $customer = $em->getRepository(Customer::class)->findOneBy(['id' => $customer->getId()]);

        return $this->response($this->customerToArray($customer), Response::HTTP_OK);
    }

    /**
     * Delete a Customer
     * @param int $id
     * @param EntityManagerInterface $em
     * @return JsonResponse
     *
     * @throws Exception
     * @Route("/api/customers/{id}", methods={"DELETE"}, name="customer_delete")
     */
    public function delete(int $id, EntityManagerInterface $em)
    {
        // Get customer by id
        $customer = Customer::notDeletedCustomerById($em, $id);

        // If there is no customer with given id
        if (! $customer) {
            return $this->response([], Response::HTTP_NOT_FOUND, false);
        }

        // Get all products of current customer
        $customer_products = $customer->getProducts();

        // Go through all products and detach them form customer
        foreach ($customer_products as $customer_product) {
            $customer->removeProduct($customer_product);
        }

        $customer->setDeletedAt(new DateTime());

        $em->flush();

        return $this->response([], Response::HTTP_OK);
    }

    /**
     * Update an existing Customer
     *
     * @param int $id
     * @param int $product_id
     * @param EntityManagerInterface $em
     * @return JsonResponse
     *
     * @Route("/api/customers/{id}/product/{product_id}", methods={"POST"}, name="customers_attach_product")
     */
    public function attachProduct(int $id, int $product_id, EntityManagerInterface $em)
    {
        // Get Product by id
        $product = Product::notDeletedProductById($em, $product_id);

        // If there is no product with given id or product deleted
        if (! $product) {
            return $this->response([], Response::HTTP_NOT_FOUND, false);
        }

        // Get Customer by id
        $customer = Customer::notDeletedCustomerById($em, $id);

        // If there is no customer with given id
        if (! $customer) {
            return $this->response([], Response::HTTP_NOT_FOUND, false);
        }

        // Attach customer to product
        $customer->addProduct($product);

        $em->flush();

        // Get just updated customer by id
        $customer = $em->getRepository(Customer::class)->findOneBy(['id' => $id]);

        return $this->response($this->customerToArray($customer), Response::HTTP_OK);
    }

    /**
     * Detach product from customer
     *
     * @param int $id
     * @param int $product_id
     * @param EntityManagerInterface $em
     * @return JsonResponse
     *
     * @Route("/api/customers/{id}/product/{product_id}", methods={"DELETE"}, name="customers_detach_product")
     */
    public function detachProduct(int $id, int $product_id, EntityManagerInterface $em)
    {
        // Get Product by id
        $product = Product::notDeletedProductById($em, $product_id);

        // If there is no product with given id or product deleted
        if (! $product) {
            return $this->response([], Response::HTTP_NOT_FOUND, false);
        }

        // Get Customer by id
        $customer = Customer::notDeletedCustomerById($em, $id);

        // If there is no customer with given id
        if (! $customer) {
            return $this->response([], Response::HTTP_NOT_FOUND, false);
        }

        // Detach customer from product
        $customer->removeProduct($product);

        $em->flush();

        // Get just updated customer by id
        $customer = $em->getRepository(Customer::class)->findOneBy(['id' => $id]);

        return $this->response($this->customerToArray($customer), Response::HTTP_OK);
    }
}
