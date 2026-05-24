<?php

namespace App\Tests\Functional;

use App\Entity\Coupon;
use App\Entity\Order;
use App\Entity\Product;
use App\Service\CartService;
use App\Tests\AbstractAppWebTestCase;

class ShopFlowTest extends AbstractAppWebTestCase
{
    private function cart(): CartService
    {
        return static::getContainer()->get(CartService::class);
    }

    private function firstProduct(): Product
    {
        $p = $this->em->getRepository(Product::class)->findOneBy([]);
        $this->assertNotNull($p, 'A product must exist in fixtures');
        return $p;
    }

    public function testShopIndexShowsProducts(): void
    {
        $this->client->request('GET', '/boutique');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Tirages');
    }

    public function testCartStartsEmpty(): void
    {
        $this->assertTrue($this->cart()->isEmpty());
        $this->assertSame(0.0, $this->cart()->getSubtotal());
    }

    public function testAddProductToCart(): void
    {
        $product = $this->firstProduct();
        $this->cart()->add($product->getId(), 2);

        $this->assertFalse($this->cart()->isEmpty());
        $this->assertSame(2, $this->cart()->getTotalQuantity());
        $expected = (float) $product->getPrice() * 2;
        $this->assertSame(round($expected, 2), $this->cart()->getSubtotal());
    }

    public function testSetQuantityZeroRemovesItem(): void
    {
        $product = $this->firstProduct();
        $this->cart()->add($product->getId(), 3);
        $this->cart()->setQuantity($product->getId(), 0);

        $this->assertTrue($this->cart()->isEmpty());
    }

    public function testClearCartEmptiesItAndDropsCoupon(): void
    {
        $product = $this->firstProduct();
        $this->cart()->add($product->getId(), 1);

        // Add a usable coupon
        $coupon = $this->makeCoupon('TEST10', Coupon::TYPE_PERCENT, '10');
        $this->em->persist($coupon);
        $this->em->flush();
        $this->assertNull($this->cart()->applyCoupon('TEST10'));

        $this->cart()->clear();

        $this->assertTrue($this->cart()->isEmpty());
        $this->assertNull($this->cart()->getCoupon());
    }

    public function testApplyPercentCouponReducesSubtotal(): void
    {
        $product = $this->firstProduct();
        $this->cart()->add($product->getId(), 1);

        $coupon = $this->makeCoupon('PERCENT20', Coupon::TYPE_PERCENT, '20');
        $this->em->persist($coupon);
        $this->em->flush();

        $error = $this->cart()->applyCoupon('PERCENT20');
        $this->assertNull($error, 'Coupon should apply without error');

        $subtotal = $this->cart()->getSubtotal();
        $expectedDiscount = round($subtotal * 0.20, 2);
        $this->assertSame($expectedDiscount, $this->cart()->getDiscount());
        $this->assertSame(round($subtotal - $expectedDiscount, 2), $this->cart()->getSubtotalAfterDiscount());
    }

    public function testApplyFixedCouponReducesSubtotal(): void
    {
        $product = $this->firstProduct();
        $this->cart()->add($product->getId(), 1);

        $coupon = $this->makeCoupon('FIXED15', Coupon::TYPE_FIXED, '15');
        $this->em->persist($coupon);
        $this->em->flush();

        $this->assertNull($this->cart()->applyCoupon('FIXED15'));
        $this->assertSame(15.0, $this->cart()->getDiscount());
    }

    public function testCouponWithMinAmountRejectedBelowThreshold(): void
    {
        $product = $this->firstProduct();
        $this->cart()->add($product->getId(), 1);

        // Min amount way above the single item price
        $coupon = $this->makeCoupon('BIGSPEND', Coupon::TYPE_PERCENT, '10');
        $coupon->setMinAmount('100000');
        $this->em->persist($coupon);
        $this->em->flush();

        $error = $this->cart()->applyCoupon('BIGSPEND');
        $this->assertNotNull($error);
        $this->assertStringContainsString('Montant minimum', $error);
    }

    public function testInvalidCouponCodeIsRejected(): void
    {
        $product = $this->firstProduct();
        $this->cart()->add($product->getId(), 1);

        $error = $this->cart()->applyCoupon('NOPE');
        $this->assertSame('Code promo introuvable.', $error);
    }

    public function testBuildOrderProducesOrderItemSnapshotAndTotals(): void
    {
        $product = $this->firstProduct();
        $this->cart()->add($product->getId(), 2);

        $order = $this->cart()->buildOrder();

        $this->assertCount(1, $order->getItems());
        $item = $order->getItems()->first();
        $this->assertSame($product->getTitle(), $item->getProductTitle());
        $this->assertSame(2, $item->getQuantity());

        $expectedSubtotal = round((float) $product->getPrice() * 2, 2);
        $this->assertSame((string) $expectedSubtotal, $order->getSubtotal());
    }

    private function makeCoupon(string $code, string $type, string $value): Coupon
    {
        $c = new Coupon();
        $c->setCode($code);
        $c->setType($type);
        $c->setValue($value);
        $c->setActive(true);
        return $c;
    }
}
