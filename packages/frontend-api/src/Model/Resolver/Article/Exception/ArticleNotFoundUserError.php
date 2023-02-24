<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Article\Exception;

use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class ArticleNotFoundUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'article-not-found';

    /**
     * @var string|null
     */
    protected ?string $articleIdentifier;

    /**
     * @param string $message
     * @param string|null $articleIdentifier
     */
    public function __construct(string $message, ?string $articleIdentifier = null)
    {
        parent::__construct($message);

        $this->articleIdentifier = $articleIdentifier;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return $this->articleIdentifier !== null ? static::CODE . '-' . $this->articleIdentifier : static::CODE;
    }
}
