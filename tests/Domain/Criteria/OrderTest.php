<?php

namespace BookManagement\Tests\Domain\Criteria;

use BookManagement\Domain\Criteria\Order;
use BookManagement\Domain\Criteria\OrderType;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase {
    /** @test */
    public function it_can_be_created_with_ascending_order(): void {
        $order = new Order('name', OrderType::ASC);

        $this->assertEquals('name', $order->orderBy());
        $this->assertEquals(OrderType::ASC, $order->orderType());
        $this->assertTrue($order->isAsc());
        $this->assertFalse($order->isDesc());
    }

    /** @test */
    public function it_can_be_created_with_descending_order(): void {
        $order = new Order('created_at', OrderType::DESC);

        $this->assertEquals('created_at', $order->orderBy());
        $this->assertEquals(OrderType::DESC, $order->orderType());
        $this->assertTrue($order->isDesc());
        $this->assertFalse($order->isAsc());
    }

    /** @test */
    public function it_can_order_by_different_fields(): void {
        $orderByName = new Order('name', OrderType::ASC);
        $orderByStatus = new Order('status', OrderType::ASC);
        $orderByDate = new Order('created_at', OrderType::DESC);

        $this->assertEquals('name', $orderByName->orderBy());
        $this->assertEquals('status', $orderByStatus->orderBy());
        $this->assertEquals('created_at', $orderByDate->orderBy());
    }
}

