<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Nette\Utils\Json;
use Override;
use Symfony\Component\Form\DataTransformerInterface;

class MailWhitelistTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function transform($value): mixed
    {
        $value['mailWhitelist'] = $this->doTransformMailWhitelist($value['mailWhitelist'] ?? null);

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function reverseTransform($value): mixed
    {
        $value['mailWhitelist'] = $this->doReverseTransformMailWhitelist($value['mailWhitelist'] ?? []);

        return $value;
    }

    /**
     * @return array<int, string>
     */
    protected function doTransformMailWhitelist(?string $item): array
    {
        if ($item === null) {
            return [];
        }

        return Json::decode($item, true);
    }

    /**
     * @param array<int, string> $item
     */
    protected function doReverseTransformMailWhitelist(array $item): ?string
    {
        if ($item === []) {
            return null;
        }

        return Json::encode(array_values($item));
    }
}
