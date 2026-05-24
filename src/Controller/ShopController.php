<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ShopController extends AbstractController
{
    #[Route('/boutique', name: 'app_shop_index')]
    public function index(ProductRepository $repository): Response
    {
        return $this->render('shop/index.html.twig', [
            'products' => $repository->findPublishedOrdered(),
        ]);
    }

    #[Route('/boutique/{slug}', name: 'app_shop_show', requirements: ['slug' => '[a-z0-9-]+'])]
    public function show(string $slug, ProductRepository $repository): Response
    {
        $product = $repository->findPublishedBySlug($slug);
        if (!$product) {
            throw $this->createNotFoundException();
        }
        return $this->render('shop/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/panier', name: 'app_cart_show')]
    public function cart(CartService $cart): Response
    {
        return $this->render('shop/cart.html.twig', [
            'items' => $cart->getDetailedItems(),
            'subtotal' => $cart->getSubtotal(),
        ]);
    }

    #[Route('/panier/ajouter/{id}', name: 'app_cart_add', requirements: ['id' => '\d+'], methods: ['POST', 'GET'])]
    public function addToCart(int $id, Request $request, CartService $cart, ProductRepository $repository): Response
    {
        $product = $repository->find($id);
        if (!$product || !$product->isPublished()) {
            throw $this->createNotFoundException();
        }
        if (!$product->isInStock()) {
            $this->addFlash('danger', 'Ce tirage n\'est plus disponible.');
            return $this->redirectToRoute('app_shop_show', ['slug' => $product->getSlug()]);
        }

        $qty = max(1, $request->request->getInt('quantity', 1));
        $cart->add($id, $qty);

        $this->addFlash('success', sprintf('"%s" ajouté au panier.', $product->getTitle()));
        return $this->redirectToRoute('app_cart_show');
    }

    #[Route('/panier/quantite/{id}', name: 'app_cart_update', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function updateQuantity(int $id, Request $request, CartService $cart): RedirectResponse
    {
        $qty = $request->request->getInt('quantity', 1);
        $cart->setQuantity($id, $qty);
        return $this->redirectToRoute('app_cart_show');
    }

    #[Route('/panier/retirer/{id}', name: 'app_cart_remove', requirements: ['id' => '\d+'])]
    public function removeFromCart(int $id, CartService $cart): RedirectResponse
    {
        $cart->remove($id);
        $this->addFlash('info', 'Article retiré du panier.');
        return $this->redirectToRoute('app_cart_show');
    }

    #[Route('/panier/vider', name: 'app_cart_clear')]
    public function clearCart(CartService $cart): RedirectResponse
    {
        $cart->clear();
        return $this->redirectToRoute('app_cart_show');
    }
}
