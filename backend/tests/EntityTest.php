<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\User;
use App\Entity\Product;

class EntityTest extends TestCase
{
    public function testUserCreation(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setName('Test User');
        $user->setPassword('hashed_password');

        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('Test User', $user->getName());
        $this->assertEquals('hashed_password', $user->getPassword());
        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertNotNull($user->getCreatedAt());
    }

    public function testProductCreation(): void
    {
        $product = new Product();
        $product->setName('Test Product');
        $product->setDescription('Test Description');
        $product->setPrice('19.99');
        $product->setStock(10);

        $this->assertEquals('Test Product', $product->getName());
        $this->assertEquals('Test Description', $product->getDescription());
        $this->assertEquals('19.99', $product->getPrice());
        $this->assertEquals(10, $product->getStock());
        $this->assertNotNull($product->getCreatedAt());
    }

    public function testProductBelongsToUser(): void
    {
        $user = new User();
        $user->setEmail('owner@example.com');
        $user->setName('Owner');
        $user->setPassword('pass');

        $product = new Product();
        $product->setName('My Product');
        $product->setDescription('Desc');
        $product->setPrice('25.00');
        $product->setStock(5);
        $product->setOwner($user);

        $this->assertSame($user, $product->getOwner());
        $this->assertEquals('owner@example.com', $product->getOwner()->getEmail());
    }

    public function testUserIdentifier(): void
    {
        $user = new User();
        $user->setEmail('user@test.com');

        $this->assertEquals('user@test.com', $user->getUserIdentifier());
    }
}
