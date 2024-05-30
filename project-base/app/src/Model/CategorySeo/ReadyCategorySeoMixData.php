<?php

declare(strict_types=1);

namespace App\Model\CategorySeo;

use Shopsys\Administration\Component\Security\AdminIdentifierInterface;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
use Symfony\Component\Validator\Constraints as Assert;

class ReadyCategorySeoMixData implements AdminIdentifierInterface
{
    /**
     * @var int|null
     */
    public $id;

    /**
     * @var int|null
     */
    public $domainId;

    /**
     * @var \App\Model\Category\Category|null
     */
    public $category;

    /**
     * @var \App\Model\Product\Flag\Flag|null
     */
    public $flag;

    /**
     * @var string|null
     */
    public $ordering;

    /**
     * @var \App\Model\CategorySeo\ReadyCategorySeoMixParameterParameterValue[]
     */
    public $readyCategorySeoMixParameterParameterValues = [];

    /**
     * @var string|null
     */
    #[Assert\NotBlank(message: 'Please enter H1')]
    public $h1;

    /**
     * @var string|null
     */
    public $shortDescription;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var string|null
     */
    public $title;

    /**
     * @var string|null
     */
    public $metaDescription;

    /**
     * @var bool
     */
    public bool $showInCategory = false;

    /**
     * @var string|null
     */
    public $choseCategorySeoMixCombinationJson;

    public UrlListData $urls;

    public ?string $categorySeoFilterFormTypeAllQueriesJson = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
