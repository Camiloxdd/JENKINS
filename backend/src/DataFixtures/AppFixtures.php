<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('test@test.com');
        $user->setName('Usuario Test');
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, 'password123')
        );

        $manager->persist($user);

        for ($i = 1; $i <= 5; $i++) {
            $product = new Product();
            $product->setName("Producto $i");
            $product->setDescription("Descripcion del producto $i");
            $product->setPrice((string) ($i * 10.50));
            $product->setStock($i * 5);
            $product->setOwner($user);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
