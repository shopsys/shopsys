<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Nette\Utils\Json;
use Override;
use Symfony\Component\Form\DataTransformerInterface;

class MailWhitelistTransformer implements DataTransformerInterface
{
    #[Override]
    public function transform($value): mixed
    {
        $value['mailWhitelist'] = $this->doTransformMailWhitelist($value['mailWhitelist'] ?? null);

        return $value;
    }

    #[Override]
    public function reverseTransform($value): mixed
    {
        $value['mailWhitelist'] = $this->doReverseTransformMailWhitelist($value['mailWhitelist'] ?? []);

        return $value;
    }

    protected function doTransformMailWhitelist(?string $item): array
    {
        if ($item === null) {
            return [];
        }

        return Json::decode($item, true);
    }

    protected function doReverseTransformMailWhitelist(array $item): ?string
    {
        if ($item === []) {
            return null;
        }

        return Json::encode(array_values($item));
    }
}
