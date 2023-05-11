<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Nette\Utils\Json;
use Symfony\Component\Form\DataTransformerInterface;

class MailWhitelistTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        $value['mailWhitelist'] = $this->doTransformMailWhitelist($value['mailWhitelist'] ?? null);

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        $value['mailWhitelist'] = $this->doReverseTransformMailWhitelist($value['mailWhitelist'] ?? []);

        return $value;
    }

    /**
     * @param string|null $item
     * @return array
     */
    protected function doTransformMailWhitelist(?string $item): array
    {
        if ($item === null) {
            return [];
        }

        return Json::decode($item, Json::FORCE_ARRAY);
    }

    /**
     * @param array $item
     * @return string|null
     */
    protected function doReverseTransformMailWhitelist(array $item): ?string
    {
        if ($item === []) {
            return null;
        }

        return Json::encode(array_values($item));
    }
}
