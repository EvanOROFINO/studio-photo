<?php

namespace App\Service;

use App\Entity\Coupon;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Repository\CouponRepository;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Session-backed shopping cart for products + coupon support.
 */
class CartService
{
    private const SESSION_KEY = 'shop_cart';
    private const COUPON_KEY = 'shop_coupon_code';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ProductRepository $productRepository,
        private readonly CouponRepository $couponRepository,
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
        $this->clearCoupon();
    }

    public function isEmpty(): bool
    {
        return empty($this->raw());
    }

    public function getTotalQuantity(): int
    {
        return array_sum($this->raw());
    }

    /** @return list<array{product: Product, quantity: int, lineTotal: float}> */
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

    // -- Coupons ---------------------------------------------------------

    public function applyCoupon(string $code): ?string
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return 'Code promo manquant.';
        }
        $coupon = $this->couponRepository->findOneByCode($code);
        if (!$coupon) {
            return 'Code promo introuvable.';
        }
        $reason = $coupon->getNotUsableReason($this->getSubtotal());
        if ($reason !== null) {
            return $reason;
        }
        $this->requestStack->getSession()->set(self::COUPON_KEY, $coupon->getCode());
        return null;
    }

    public function clearCoupon(): void
    {
        $this->requestStack->getSession()->remove(self::COUPON_KEY);
    }

    public function getCoupon(): ?Coupon
    {
        $code = $this->requestStack->getSession()->get(self::COUPON_KEY);
        if (!$code) {
            return null;
        }
        $coupon = $this->couponRepository->findOneByCode($code);
        if (!$coupon || !$coupon->isUsable($this->getSubtotal())) {
            $this->clearCoupon();
            return null;
        }
        return $coupon;
    }

    public function getDiscount(): float
    {
        $coupon = $this->getCoupon();
        if (!$coupon) {
            return 0.0;
        }
        return $coupon->computeDiscount($this->getSubtotal());
    }

    public function getSubtotalAfterDiscount(): float
    {
        return max(0.0, round($this->getSubtotal() - $this->getDiscount(), 2));
    }

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

        $discount = $this->getDiscount();
        if ($discount > 0) {
            $newSubtotal = max(0, (float) $order->getSubtotal() - $discount);
            $order->setSubtotal((string) round($newSubtotal, 2));
            $order->setTotalAmount((string) round($newSubtotal + (float) $order->getShippingFee(), 2));

            $coupon = $this->getCoupon();
            $note = sprintf(
                'Code promo : %s (%s, -%s €)',
                $coupon?->getCode() ?? '?',
                $coupon?->getHumanLabel() ?? '?',
                number_format($discount, 2, ',', ' '),
            );
            $order->setNotes(trim(($order->getNotes() ?? '')."\n".$note));
        }

        return $order;
    }

    /** @return array<int, int> */
    private function raw(): array
    {
        return $this->requestStack->getSession()->get(self::SESSION_KEY, []);
    }

    /** @param array<int, int> $cart */
    private function save(array $cart): void
    {
        $this->requestStack->getSession()->set(self::SESSION_KEY, $cart);
    }
}
