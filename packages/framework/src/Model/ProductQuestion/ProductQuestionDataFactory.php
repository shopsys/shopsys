<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductQuestion;

class ProductQuestionDataFactory
{
    protected function createInstance(): ProductQuestionData
    {
        return new ProductQuestionData();
    }

    public function create(int $domainId): ProductQuestionData
    {
        $productQuestionData = $this->createInstance();
        $productQuestionData->domainId = $domainId;

        return $productQuestionData;
    }
}
