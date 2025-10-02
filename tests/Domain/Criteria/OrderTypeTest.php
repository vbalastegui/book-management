<?php

namespace BookManagement\Tests\Domain\Criteria;

use BookManagement\Domain\Criteria\OrderType;
use PHPUnit\Framework\TestCase;

class OrderTypeTest extends TestCase {
    /** @test */
    public function it_has_ascending_type(): void {
        $this->assertEquals('ASC', OrderType::ASC->value);
    }

    /** @test */
    public function it_has_descending_type(): void {
        $this->assertEquals('DESC', OrderType::DESC->value);
    }

    /** @test */
    public function it_can_be_compared(): void {
        $type1 = OrderType::ASC;
        $type2 = OrderType::ASC;
        $type3 = OrderType::DESC;

        $this->assertEquals($type1, $type2);
        $this->assertNotEquals($type1, $type3);
    }
}

