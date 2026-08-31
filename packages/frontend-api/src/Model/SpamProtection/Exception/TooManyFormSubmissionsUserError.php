<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\SpamProtection\Exception;

use GraphQL\Error\UserError;
use Override;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class TooManyFormSubmissionsUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'too-many-form-submissions';

    public function __construct(string $message = 'Too many submissions of this form. Try again later.')
    {
        parent::__construct($message);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
