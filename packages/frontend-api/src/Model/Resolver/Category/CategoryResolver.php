<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Category;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException;

class CategoryResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    protected $categoryFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(
        CategoryFacade $categoryFacade
    ) {
        $this->categoryFacade = $categoryFacade;
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function resolver(string $uuid): Category
    {
        if (Uuid::isValid($uuid) === false) {
            throw new UserError('Provided argument is not valid UUID.');
        }

        try {
            return $this->categoryFacade->getByUuid($uuid);
        } catch (CategoryNotFoundException $categoryNotFoundException) {
            throw new UserError($categoryNotFoundException->getMessage());
        }
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolver' => 'category',
        ];
    }
}
