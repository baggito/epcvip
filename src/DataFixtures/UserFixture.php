<?php

namespace App\DataFixtures;

use App\Entity\ApiToken;
use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends BaseFixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
     {
         $this->passwordEncoder = $passwordEncoder;
     }

    protected function loadData(ObjectManager $em)
    {
        $this->createMany(User::class, 10, function (User $user, $count) use ($em) {
            $user->setEmail(sprintf('user%d@example.com', $count));
            $user->setFirstName($this->faker->firstName);

            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                'password123'
             ));

            $apiToken1 = new ApiToken($user);
            $apiToken2 = new ApiToken($user);

            $em->persist($apiToken1);
            $em->persist($apiToken2);
        });

        $em->flush();
    }
}
