<?php

namespace BookManagement\Tests\Domain\Criteria;

use BookManagement\Domain\Criteria\Filter;
use BookManagement\Domain\Criteria\FilterOperator;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase {
    /** @test */
    public function it_can_be_created_with_contains_operator(): void {
        $filter = new Filter('name', FilterOperator::CONTAINS, 'value');

        $this->assertEquals('name', $filter->field());
        $this->assertEquals(FilterOperator::CONTAINS, $filter->operator());
        $this->assertEquals('value', $filter->value());
    }

    /** @test */
    public function it_can_be_created_with_equal_operator(): void {
        $filter = new Filter('status', FilterOperator::EQUAL, 'active');

        $this->assertEquals('status', $filter->field());
        $this->assertEquals(FilterOperator::EQUAL, $filter->operator());
        $this->assertEquals('active', $filter->value());
    }

    /** @test */
    public function it_can_be_created_with_greater_than_operator(): void {
        $filter = new Filter('age', FilterOperator::GREATER_THAN, 18);

        $this->assertEquals('age', $filter->field());
        $this->assertEquals(FilterOperator::GREATER_THAN, $filter->operator());
        $this->assertEquals(18, $filter->value());
    }

    /** @test */
    public function it_can_be_created_with_less_than_operator(): void {
        $filter = new Filter('price', FilterOperator::LESS_THAN, 100);

        $this->assertEquals('price', $filter->field());
        $this->assertEquals(FilterOperator::LESS_THAN, $filter->operator());
        $this->assertEquals(100, $filter->value());
    }

    /** @test */
    public function it_can_handle_string_values(): void {
        $filter = new Filter('code', FilterOperator::EQUAL, 'ABC123');

        $this->assertIsString($filter->value());
        $this->assertEquals('ABC123', $filter->value());
    }

    /** @test */
    public function it_can_handle_integer_values(): void {
        $filter = new Filter('quantity', FilterOperator::EQUAL, 42);

        $this->assertIsInt($filter->value());
        $this->assertEquals(42, $filter->value());
    }
}

