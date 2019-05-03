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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ProductController extends BaseController
{
    /**
     * Returns all customers
     *
     * @param EntityManagerInterface $em
     * @return JsonResponse
     *
     * @Route("/api/products", methods={"GET"}, name="products_index")
     */
    public function index(EntityManagerInterface $em)
    {
        // Get Product by id
        $products = Product::notDeletedProducts($em);

        // If there is no product with given id or product deleted
        if (! $products) {
            return $this->response([], Response::HTTP_NOT_FOUND, false);
        }

        // Data for response
        $data = [];

        // Generate user friendly response
        foreach ($products as $product) {
            $data[] = $this->productToArray($product);
        }

        // Call to log the data
//        $this->logInfo('products', $data);

        return $this->response($data, Response::HTTP_OK);
    }

    /**
     * Extract Product object (could be an array of objects) into array
     *
     * @param Product $product
     * @return array
     */
    public function productToArray(Product $product)
    {
        return [
            'id'         => $product->getId(),
            'customer'   => (new CustomerController())->customerToArray($product->getCustomer()),
            'name'       => $product->getName(),
            'status'     => $product->getStatus(),
            'created_at' => $product->getCreatedAt()
        ];
    }

    /**
     * Generate unique issn
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
            $product = $em->getRepository(Product::class)->findBy(['issn' => $uuid]);
        } while ($product);

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
            'name' => [
                new Assert\NotBlank,
                new Assert\Length(['min' => 2])
            ],
            'status' => [
                // Merge empty value with product statuses, that will allow user to send empty status,
                // by default if status is empty we set it 'new'
                new Assert\Choice(array_merge(Product::$product_statuses, [''])),
            ]
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
     * Create a new Product
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return JsonResponse
     *
     * @Route("/api/products", methods={"POST"}, name="products_create")
     */
    public function create(EntityManagerInterface $em, Request $request, ValidatorInterface $validator)
    {
        // Validate request
        $validation = $this->validateRequest($request, $validator);

        // Return validation error messages if validation failed
        if (isset($validation['error'])) {
            return $this->response($validation['error'], Response::HTTP_BAD_REQUEST, false);
        }

        $product = new Product();
        $product->setIssn($this->generateUuid($em));
        $product->setName($request->request->get('name'));
        $product->setStatus($request->request->get('status'));

        // If customer id is specified then we attach product to customer
        if ($customer_id = $request->request->get('customer_id')) {
            // Get customer by id
            $customer = $em->getRepository(Customer::class)->findOneBy(['id' => $customer_id]);

            // If there is no customer with given id
            if (! $customer) {
                return $this->response([], Response::HTTP_NOT_FOUND, false);
            }

            // Attach customer to product
            $product->setCustomer($customer);
        }

        $em->persist($product);
        $em->flush();

        // Get just created product by id
        $product = $em->getRepository(Product::class)->findOneBy(['id' => $product->getId()]);

        return $this->response($this->productToArray($product), Response::HTTP_OK);
    }

    /**
     * Retrieve an existing Product
     *
     * @param int $id
     * @param EntityManagerInterface $em
     * @return JsonResponse
     *
     * @Route("/api/products/{id}", methods={"GET"}, name="products_read")
     */
    public function read(int $id, EntityManagerInterface $em)
    {
        // Get Product by id
        $product = Product::notDeletedProductById($em, $id);

        // If there is no product with given id or product deleted
        if (! $product) {
            return $this->response([], Response::HTTP_NOT_FOUND, false);
        }

        return $this->response($this->productToArray($product), Response::HTTP_OK);
    }

    /**
     * Update an existing Product
     *
     * @param int $id
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return JsonResponse
     *
     * @Route("/api/products/{id}", methods={"POST"}, name="products_update")
     */
    public function update(int $id, EntityManagerInterface $em, Request $request, ValidatorInterface $validator)
    {
        // Get Product by id
        $product = Product::notDeletedProductById($em, $id);

        // If there is no product with given id or product deleted
        if (! $product) {
            return $this->response([], Response::HTTP_NOT_FOUND, false);
        }

        // Validate request
        $validation = $this->validateRequest($request, $validator);

        // Return validation error messages if validation failed
        if (isset($validation['error'])) {
            return $this->response($validation['error'], Response::HTTP_BAD_REQUEST, false);
        }

        $product->setIssn($this->generateUuid($em));
        $product->setName($request->request->get('name'));
        $product->setStatus($request->request->get('status'));

        // If customer id is specified then we attach product to customer
        if ($customer_id = $request->request->get('customer_id')) {
            // Get customer by id
            $customer = $em->getRepository(Customer::class)->findOneBy(['id' => $customer_id]);

            // If there is no customer with given id
            if (! $customer) {
                return $this->response([], Response::HTTP_NOT_FOUND, false);
            }

            // Attach customer to product
            $product->setCustomer($customer);
        }

        $em->flush();

        // Get just created product by id
        $product = $em->getRepository(Product::class)->findOneBy(['id' => $id]);

        return $this->response($this->productToArray($product), Response::HTTP_OK);
    }

    /**
     * Delete a Product
     * @param int $id
     * @param EntityManagerInterface $em
     * @return JsonResponse
     * @throws Exception
     *
     * @Route("/api/products/{id}", methods={"DELETE"}, name="products_delete")
     */
    public function delete(int $id, EntityManagerInterface $em)
    {
        // Get Product by id
        $product = Product::notDeletedProductById($em, $id);

        // If there is no product with given id or product deleted
        if (! $product) {
            return $this->response([], Response::HTTP_NOT_FOUND, false);
        }

        // Get customer of current product
        $product_customer = $product->getCustomer();

        // Detach product from customer
        $product_customer->removeProduct($product);

        $product->setDeletedAt(new DateTime());

        $em->flush();

        return $this->response([], Response::HTTP_OK);
    }

    /**
     * Update an existing Customer
     *
     * @param int $id
     * @param int $customer_id
     * @param EntityManagerInterface $em
     * @return JsonResponse
     *
     * @Route("/api/products/{id}/customer/{customer_id}", methods={"POST"}, name="products_attach_customer")
     */
    public function attachToCustomer(int $id, int $customer_id, EntityManagerInterface $em)
    {
        // Get Product by id
        $product = Product::notDeletedProductById($em, $id);

        // If there is no product with given id or product deleted
        if (! $product) {
            return $this->response([], Response::HTTP_NOT_FOUND, false);
        }

        // Get Customer by id
        $customer = Customer::notDeletedCustomerById($em, $customer_id);

        // If there is no customer with given id
        if (! $customer) {
            return $this->response([], Response::HTTP_NOT_FOUND, false);
        }

        // Attach customer to product
        $product->setCustomer($customer);

        $em->flush();

        // Get just created customer by id
        $product = $em->getRepository(Product::class)->findOneBy(['id' => $id]);

        return $this->response($this->productToArray($product), Response::HTTP_OK);
    }

    /**
     * Update an existing Customer
     *
     * @param int $id
     * @param EntityManagerInterface $em
     * @return JsonResponse
     *
     * @Route("/api/products/{id}/customer", methods={"DELETE"}, name="products_detach_customer")
     */
    public function detachFromCustomer(int $id, EntityManagerInterface $em)
    {
        // Get Product by id
        $product = Product::notDeletedProductById($em, $id);

        // If there is no product with given id or product deleted
        if (! $product) {
            return $this->response([], Response::HTTP_NOT_FOUND, false);
        }

        // Detach customer from product
        $product->setCustomer(null);

        $em->flush();

        // Get just created customer by id
        $product = $em->getRepository(Product::class)->findOneBy(['id' => $id]);

        return $this->response($this->productToArray($product), Response::HTTP_OK);
    }

    /**
     * Return rendered html page with the pending products
     *
     * @param EntityManagerInterface $em
     * @return string
     */
    public function renderPendingProductsForEmail(EntityManagerInterface $em)
    {
        // Get formatted date for 2 weeks ago
        $week_ago = date('Y-m-d H:i:s', strtotime('-16 weeks'));
        // Get not deleted products created until 2 weak ago
        $products = Product::getPendingProducts($em, $week_ago);

        return $this->renderView('email/index.html.twig', [
            'products' => $products
        ]);
    }
}
