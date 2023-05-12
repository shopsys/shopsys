<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Article\Exception;

use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class ArticleNotFoundUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'article-not-found';

    /**
     * @param string $message
     * @param string|null $articleIdentifier
     */
    public function __construct(string $message, protected readonly ?string $articleIdentifier = null)
    {
        parent::__construct($message);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return $this->articleIdentifier !== null ? static::CODE . '-' . $this->articleIdentifier : static::CODE;
    }
}
