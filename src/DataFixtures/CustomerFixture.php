<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use Doctrine\Common\Persistence\ObjectManager;
use PhpParser\Node\Expr\Cast\Object_;

class CustomerFixture extends BaseFixture
{
    private static $customer_statuses = [
        'new', 'pending', 'in review', 'approved', 'inactive', 'deleted'
    ];

    // Count of fake records
    private $fixture_count = 10;

    /**
     * @return int
     */
    public function getFixtureCount(): int
    {
        return $this->fixture_count;
    }

    protected function loadData(ObjectManager $em)
    {
        $this->createMany(Customer::class, $this->fixture_count, function (Customer $customer, $count) {
            // Initialize uuid with fake unique uuid
            $customer->setUuid($this->faker->uuid);
            // Initialize firstName with fake firstName
            $customer->setFirstName($this->faker->firstName);
            // Initialize firstName with fake firstName
            $customer->setLastName($this->faker->lastName);
            // Initialize dateOfBirth with fake date
            $customer->setDateOfBirth($this->faker->dateTimeBetween('-100 years', '-1 years'));
            // Initialize customer status with fake status form predefined values
            $customer->setStatus($this->faker->randomElement(self::$customer_statuses));
            // Initialize customer createdAt with fake date from last 100 days
            $customer->setCreatedAt($this->faker->dateTimeBetween('-100 days', '-1 days'));

            // Initialize updated_at of 30% of records
            if ($this->faker->boolean(30)) {
                $customer->setUpdatedAt($this->faker->dateTimeBetween('-100 days', '-1 days'));
            }

            // Initialize deleted of 20% of records
            if ($this->faker->boolean(30)) {
                $customer->setDeletedAt($this->faker->dateTimeBetween('-100 days', '-1 days'));
            }
        });

        $em->flush();
    }
}
