<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/products')]
class ProductController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private NormalizerInterface $normalizer
    ) {}

    #[Route('', name: 'product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->findBy(
            ['owner' => $this->getUser()],
            ['createdAt' => 'DESC']
        );

        $data = $this->normalizer->normalize($products, null, ['groups' => ['product:read']]);

        return $this->json($data);
    }

    #[Route('', name: 'product_create', methods: ['POST'])]
    public function create(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || empty($data['name']) || empty($data['price'])) {
            return $this->json([
                'error' => 'Nombre y precio son obligatorios'
            ], Response::HTTP_BAD_REQUEST);
        }

        $product = new Product();
        $product->setName($data['name']);
        $product->setDescription($data['description'] ?? '');
        $product->setPrice((string) $data['price']);
        $product->setStock($data['stock'] ?? 0);
        $product->setOwner($this->getUser());
        $product->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $result = $this->normalizer->normalize($product, null, ['groups' => ['product:read']]);

        return $this->json($result, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'product_show', methods: ['GET'])]
    public function show(Product $product): JsonResponse
    {
        if ($product->getOwner() !== $this->getUser()) {
            return $this->json(['error' => 'No autorizado'], Response::HTTP_FORBIDDEN);
        }

        $result = $this->normalizer->normalize($product, null, ['groups' => ['product:read']]);

        return $this->json($result);
    }

    #[Route('/{id}', name: 'product_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, Product $product): JsonResponse
    {
        if ($product->getOwner() !== $this->getUser()) {
            return $this->json(['error' => 'No autorizado'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $product->setName($data['name']);
        }
        if (isset($data['description'])) {
            $product->setDescription($data['description']);
        }
        if (isset($data['price'])) {
            $product->setPrice((string) $data['price']);
        }
        if (isset($data['stock'])) {
            $product->setStock((int) $data['stock']);
        }

        $product->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        $result = $this->normalizer->normalize($product, null, ['groups' => ['product:read']]);

        return $this->json($result);
    }

    #[Route('/{id}', name: 'product_delete', methods: ['DELETE'])]
    public function delete(Product $product, ProductRepository $productRepository): JsonResponse
    {
        if ($product->getOwner() !== $this->getUser()) {
            return $this->json(['error' => 'No autorizado'], Response::HTTP_FORBIDDEN);
        }

        $productRepository->remove($product, true);

        return $this->json(['message' => 'Producto eliminado exitosamente']);
    }
}
