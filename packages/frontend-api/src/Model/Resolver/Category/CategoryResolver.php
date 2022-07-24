<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Category;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\DependencyInjection\SetterInjectionTrait;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\Exception\CategoryNotFoundUserError;

class CategoryResolver implements ResolverInterface, AliasedInterface
{
    use SetterInjectionTrait;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    protected $categoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain|null
     */
    protected ?Domain $domain;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade|null
     */
    protected ?FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain|null $domain
     * @param \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade|null $friendlyUrlFacade
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        ?Domain $domain = null,
        ?FriendlyUrlFacade $friendlyUrlFacade = null
    ) {
        $this->categoryFacade = $categoryFacade;
        $this->domain = $domain;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setDomain(Domain $domain): void
    {
        $this->setDependency($domain, 'domain');
    }

    /**
     * @required
     * @param \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setFriendlyUrlFacade(FriendlyUrlFacade $friendlyUrlFacade): void
    {
        $this->setDependency($friendlyUrlFacade, 'friendlyUrlFacade');
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     * @deprecated This method will be removed in next major version. It has been replaced by resolveByUuidOrUrlSlug
     */
    public function resolver(string $uuid): Category
    {
        DeprecationHelper::triggerMethod(__METHOD__, 'resolveByUuidOrUrlSlug');

        try {
            return $this->categoryFacade->getVisibleOnDomainByUuid($this->domain->getId(), $uuid);
        } catch (CategoryNotFoundException $categoryNotFoundException) {
            throw new CategoryNotFoundUserError($categoryNotFoundException->getMessage());
        }
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function resolveByUuidOrUrlSlug(?string $uuid = null, ?string $urlSlug = null): Category
    {
        if ($uuid !== null) {
            return $this->getByUuid($uuid);
        }

        if ($urlSlug !== null) {
            return $this->getVisibleOnDomainAndSlug($urlSlug);
        }

        throw new UserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolver' => 'category',
            'resolveByUuidOrUrlSlug' => 'categoryByUuidOrUrlSlug',
        ];
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    protected function getByUuid(string $uuid): Category
    {
        try {
            return $this->categoryFacade->getByUuid($uuid);
        } catch (CategoryNotFoundException $categoryNotFoundException) {
            throw new CategoryNotFoundUserError($categoryNotFoundException->getMessage());
        }
    }

    /**
     * @param string $urlSlug
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    protected function getVisibleOnDomainAndSlug(string $urlSlug): Category
    {
        try {
            $friendlyUrl = $this->friendlyUrlFacade->getFriendlyUrlByRouteNameAndSlug(
                $this->domain->getId(),
                'front_product_list',
                $urlSlug
            );

            return $this->categoryFacade->getVisibleOnDomainById($this->domain->getId(), $friendlyUrl->getEntityId());
        } catch (FriendlyUrlNotFoundException | CategoryNotFoundException $categoryNotFoundException) {
            throw new CategoryNotFoundUserError('Category with URL slug `' . $urlSlug . '` does not exist.');
        }
    }
}
