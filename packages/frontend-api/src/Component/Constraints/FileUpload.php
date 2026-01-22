<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;

class FileUpload extends Constraint
{
    public const string TOO_BIG_ERROR = '6d2b62ea-d252-4470-92c4-13f2fe17f24a';
    public const string UPLOAD_ERROR = 'f31ba2f1-8fa2-4f0a-9fcf-e2c47a602dcf';
    public const string MIMETYPE_ERROR = '7078e0f9-8415-4247-91d6-5ae23c2629cb';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::TOO_BIG_ERROR => 'TOO_BIG_ERROR',
        self::UPLOAD_ERROR => 'UPLOAD_ERROR',
        self::MIMETYPE_ERROR => 'MIMETYPE_ERROR',
    ];

    /**
     * @param array|string $mimeTypes
     * @param int|null $maxSize
     * @param string $mimeTypesMessage
     * @param string $maxSizeMessage
     * @param string $uploadErrorMessage
     * @param array|null $groups
     * @param mixed $payload
     */
    public function __construct(
        public array|string $mimeTypes = 'image/*',
        public ?int $maxSize = null,
        public string $mimeTypesMessage = 'Type of file {{ fileName }} is unsupported.',
        public string $maxSizeMessage = 'The file {{ fileName }} is too big.',
        public string $uploadErrorMessage = 'Error occurred while uploading file.',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct([], $groups, $payload);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
