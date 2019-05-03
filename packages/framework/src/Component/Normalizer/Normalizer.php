<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Normalizer;

use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

class Normalizer
{
    /**
     * @var \Symfony\Component\Serializer\Serializer
     */
    protected $serializer;

    public function __construct()
    {
        $this->serializer = new SymfonySerializer([new ArrayDenormalizer(), new GetSetMethodNormalizer()]);
    }

    /**
     * @param array $array Data to restore
     * @param string $type The expected class to instantiate
     * @return object[] array of instances of $type
     */
    public function denormalizeArray(array $array, string $type): array
    {
        return $this->serializer->denormalize($array, $this->resolveType($type), 'array');
    }

    /**
     * @param string $type
     * @return string
     */
    protected function resolveType(string $type): string
    {
        return $type . '[]';
    }
}
