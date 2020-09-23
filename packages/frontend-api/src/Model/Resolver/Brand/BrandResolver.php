<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Brand;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\Exception\BrandNotFoundException;

class BrandResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade
     */
    protected $brandFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     */
    public function __construct(
        BrandFacade $brandFacade
    ) {
        $this->brandFacade = $brandFacade;
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    public function resolver(string $uuid): Brand
    {
        if (Uuid::isValid($uuid) === false) {
            throw new UserError('Provided argument is not valid UUID.');
        }

        try {
            return $this->brandFacade->getByUuid($uuid);
        } catch (BrandNotFoundException $brandNotFoundException) {
            throw new UserError($brandNotFoundException->getMessage());
        }
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolver' => 'brand',
        ];
    }
}
