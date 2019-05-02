<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ProductFixture extends BaseFixture
{
    private static $product_statuses = [
        'new', 'pending', 'in review', 'approved', 'inactive', 'deleted'
    ];

    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(Product::class, 30, function (Product $product, $count) {
            // Initialize issn with fake unique uuid
            $product->setIssn($this->faker->uuid);
            // Initialize product name with fake name
            $product->setName($this->faker->name);
            // Initialize product status with fake status form predefined values
            $product->setStatus($this->faker->randomElement(self::$product_statuses));
            // Initialize product createdAt with fake date from last 100 days
            $product->setCreatedAt($this->faker->dateTimeBetween('-100 days', '-1 days'));

            // Initialize updated_at of 30% of records
            if ($this->faker->boolean(30)) {
                $product->setUpdatedAt($this->faker->dateTimeBetween('-100 days', '-1 days'));
            }

            // Initialize deleted_at of 30% of records
            if ($this->faker->boolean(30)) {
                $product->setDeletedAt($this->faker->dateTimeBetween('-100 days', '-1 days'));
            }

            // Get the number of all customers
            $customers_count = (new CustomerFixture())->getFixtureCount();
            // $count % $customers_count = the index of fake customer that doesn't exceed max index of customers
            $product->setCustomer($this->getReference(Customer::class .'_'. $count%$customers_count));
        });

        $manager->flush();
    }
}
