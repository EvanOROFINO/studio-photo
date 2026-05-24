<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Session-backed shopping cart for products.
 *
 * The cart stores only `[productId => quantity]` pairs in the session, so we
 * always re-query the database for fresh price + stock and prevent tampering.
 */
class CartService
{
    private const SESSION_KEY = 'shop_cart';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ProductRepository $productRepository,
    ) {
    }

    public function add(int $productId, int $quantity = 1): void
    {
        if ($quantity < 1) {
            return;
        }
        $cart = $this->raw();
        $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;
        $this->save($cart);
    }

    public function setQuantity(int $productId, int $quantity): void
    {
        $cart = $this->raw();
        if ($quantity <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $quantity;
        }
        $this->save($cart);
    }

    public function remove(int $productId): void
    {
        $cart = $this->raw();
        unset($cart[$productId]);
        $this->save($cart);
    }

    public function clear(): void
    {
        $this->save([]);
    }

    public function isEmpty(): bool
    {
        return empty($this->raw());
    }

    public function getTotalQuantity(): int
    {
        return array_sum($this->raw());
    }

    /**
     * @return list<array{product: Product, quantity: int, lineTotal: float}>
     */
    public function getDetailedItems(): array
    {
        $raw = $this->raw();
        if (empty($raw)) {
            return [];
        }

        $products = $this->productRepository->findBy(['id' => array_keys($raw)]);
        $items = [];
        foreach ($products as $product) {
            $qty = $raw[$product->getId()] ?? 0;
            if ($qty < 1 || !$product->isPublished()) {
                continue;
            }
            $items[] = [
                'product' => $product,
                'quantity' => $qty,
                'lineTotal' => $product->getPriceAsFloat() * $qty,
            ];
        }
        return $items;
    }

    public function getSubtotal(): float
    {
        $total = 0.0;
        foreach ($this->getDetailedItems() as $item) {
            $total += $item['lineTotal'];
        }
        return round($total, 2);
    }

    /**
     * Materialise the current cart into a brand new Order with OrderItems.
     * Does NOT persist — the caller is in charge of em->persist() + flush().
     */
    public function buildOrder(): Order
    {
        $order = new Order();
        foreach ($this->getDetailedItems() as $item) {
            /** @var Product $product */
            $product = $item['product'];

            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setProductTitle($product->getTitle() ?? '');
            $orderItem->setProductFormat($product->getFormat());
            $orderItem->setUnitPrice($product->getPrice() ?? '0');
            $orderItem->setQuantity($item['quantity']);

            $order->addItem($orderItem);
        }
        $order->recalculateTotals();
        return $order;
    }

    /** @return array<int, int> */
    private function raw(): array
    {
        $session = $this->requestStack->getSession();
        return $session->get(self::SESSION_KEY, []);
    }

    /** @param array<int, int> $cart */
    private function save(array $cart): void
    {
        $session = $this->requestStack->getSession();
        $session->set(self::SESSION_KEY, $cart);
    }
}
