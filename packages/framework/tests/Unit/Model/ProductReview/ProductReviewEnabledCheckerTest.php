<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\ProductReview;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewEnabledChecker;

final class ProductReviewEnabledCheckerTest extends TestCase
{
    public function testReviewsAreEnabledOnlyForConfiguredDomains(): void
    {
        $productReviewEnabledChecker = new ProductReviewEnabledChecker([1, 3]);

        $this->assertTrue($productReviewEnabledChecker->isEnabledForDomain(1));
        $this->assertFalse($productReviewEnabledChecker->isEnabledForDomain(2));
        $this->assertTrue($productReviewEnabledChecker->isEnabledForDomain(3));
    }
}
