<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

class FileUpload extends Constraint
{
    public const string TOO_BIG_ERROR = '6d2b62ea-d252-4470-92c4-13f2fe17f24a';
    public const string UPLOAD_ERROR = 'f31ba2f1-8fa2-4f0a-9fcf-e2c47a602dcf';
    public const string MIMETYPE_ERROR = '7078e0f9-8415-4247-91d6-5ae23c2629cb';
    public const string EXTENSION_ERROR = '4f9e30cd-3a37-4095-9432-2537d0299cf2';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::TOO_BIG_ERROR => 'TOO_BIG_ERROR',
        self::UPLOAD_ERROR => 'UPLOAD_ERROR',
        self::MIMETYPE_ERROR => 'MIMETYPE_ERROR',
        self::EXTENSION_ERROR => 'EXTENSION_ERROR',
    ];

    /**
     * @param array<string, mixed>|null $options
     * @param array<string>|string $mimeTypes
     * @param array<string>|null $extensions
     * @param array<string>|null $groups
     */
    #[HasNamedArguments]
    public function __construct(
        ?array $options = null,
        public array|string $mimeTypes = 'image/*',
        public ?array $extensions = null,
        public ?int $maxSize = null,
        public string $mimeTypesMessage = 'Type of file {{ fileName }} is unsupported.',
        public string $extensionsMessage = 'The file {{ fileName }} has an unsupported extension.',
        public string $maxSizeMessage = 'The file {{ fileName }} is too big.',
        public string $uploadErrorMessage = 'Error occurred while uploading file.',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        if (is_array($options)) {
            DeprecationHelper::trigger(
                'Passing an array of options to configure the "%s" constraint is deprecated, use named arguments instead.',
                static::class,
            );
        }

        parent::__construct($options, $groups, $payload);
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
