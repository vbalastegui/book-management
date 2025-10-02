<?php

namespace BookManagement\Tests\Domain\Criteria;

use BookManagement\Domain\Criteria\FilterOperator;
use PHPUnit\Framework\TestCase;

class FilterOperatorTest extends TestCase {
    /** @test */
    public function it_has_equal_operator(): void {
        $this->assertEquals('EQUAL', FilterOperator::EQUAL->value);
    }

    /** @test */
    public function it_has_not_equal_operator(): void {
        $this->assertEquals('NOT_EQUAL', FilterOperator::NOT_EQUAL->value);
    }

    /** @test */
    public function it_has_greater_than_operator(): void {
        $this->assertEquals('GREATER_THAN', FilterOperator::GREATER_THAN->value);
    }

    /** @test */
    public function it_has_greater_than_or_equal_operator(): void {
        $this->assertEquals('GREATER_THAN_OR_EQUAL', FilterOperator::GREATER_THAN_OR_EQUAL->value);
    }

    /** @test */
    public function it_has_less_than_operator(): void {
        $this->assertEquals('LESS_THAN', FilterOperator::LESS_THAN->value);
    }

    /** @test */
    public function it_has_less_than_or_equal_operator(): void {
        $this->assertEquals('LESS_THAN_OR_EQUAL', FilterOperator::LESS_THAN_OR_EQUAL->value);
    }

    /** @test */
    public function it_has_contains_operator(): void {
        $this->assertEquals('CONTAINS', FilterOperator::CONTAINS->value);
    }

    /** @test */
    public function it_has_not_contains_operator(): void {
        $this->assertEquals('NOT_CONTAINS', FilterOperator::NOT_CONTAINS->value);
    }

    /** @test */
    public function it_has_in_operator(): void {
        $this->assertEquals('IN', FilterOperator::IN->value);
    }

    /** @test */
    public function it_has_not_in_operator(): void {
        $this->assertEquals('NOT_IN', FilterOperator::NOT_IN->value);
    }

    /** @test */
    public function it_can_be_compared(): void {
        $operator1 = FilterOperator::EQUAL;
        $operator2 = FilterOperator::EQUAL;
        $operator3 = FilterOperator::CONTAINS;

        $this->assertEquals($operator1, $operator2);
        $this->assertNotEquals($operator1, $operator3);
    }
}

