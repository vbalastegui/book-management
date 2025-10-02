<?php

namespace BookManagement\Tests\Domain\Criteria;

use BookManagement\Domain\Criteria\Criteria;
use BookManagement\Domain\Criteria\Filter;
use BookManagement\Domain\Criteria\FilterOperator;
use BookManagement\Domain\Criteria\Order;
use BookManagement\Domain\Criteria\OrderType;
use PHPUnit\Framework\TestCase;

class CriteriaTest extends TestCase {
    /** @test */
    public function it_can_be_created_empty(): void {
        $criteria = new Criteria();

        $this->assertFalse($criteria->hasFilters());
        $this->assertEmpty($criteria->filters());
        $this->assertNull($criteria->order());
        $this->assertNull($criteria->limit());
        $this->assertNull($criteria->offset());
    }

    /** @test */
    public function it_can_be_created_with_filters(): void {
        $filter = new Filter('title', FilterOperator::CONTAINS, 'Clean');
        $criteria = new Criteria([$filter]);

        $this->assertTrue($criteria->hasFilters());
        $this->assertCount(1, $criteria->filters());
        $this->assertEquals($filter, $criteria->filters()[0]);
    }

    /** @test */
    public function it_can_be_created_with_multiple_filters(): void {
        $filter1 = new Filter('title', FilterOperator::CONTAINS, 'Clean');
        $filter2 = new Filter('author', FilterOperator::EQUAL, 'Martin');
        $criteria = new Criteria([$filter1, $filter2]);

        $this->assertTrue($criteria->hasFilters());
        $this->assertCount(2, $criteria->filters());
    }

    /** @test */
    public function it_can_be_created_with_order(): void {
        $order = new Order('title', OrderType::ASC);
        $criteria = new Criteria([], $order);

        $this->assertEquals($order, $criteria->order());
    }

    /** @test */
    public function it_can_be_created_with_limit(): void {
        $criteria = new Criteria([], null, 10);

        $this->assertEquals(10, $criteria->limit());
    }

    /** @test */
    public function it_can_be_created_with_offset(): void {
        $criteria = new Criteria([], null, null, 20);

        $this->assertEquals(20, $criteria->offset());
    }

    /** @test */
    public function it_can_be_created_with_all_parameters(): void {
        $filter = new Filter('title', FilterOperator::CONTAINS, 'Code');
        $order = new Order('publication_year', OrderType::DESC);
        $criteria = new Criteria([$filter], $order, 10, 5);

        $this->assertTrue($criteria->hasFilters());
        $this->assertCount(1, $criteria->filters());
        $this->assertEquals($order, $criteria->order());
        $this->assertEquals(10, $criteria->limit());
        $this->assertEquals(5, $criteria->offset());
    }
}

