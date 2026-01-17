<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Token;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class JwtConfigurationProvider
{
    protected const string FRONTEND_API_KEYS_FILEPATH_PARAMETER = 'shopsys.frontend_api.keys_filepath';

    protected ?Configuration $configuration = null;

    public function __construct(
        protected readonly ParameterBagInterface $parameterBag,
        protected readonly Domain $domain,
    ) {
    }

    public function getConfiguration(): Configuration
    {
        if ($this->configuration !== null) {
            return $this->configuration;
        }

        $this->configuration = Configuration::forAsymmetricSigner(
            $this->getSigner(),
            $this->getPrivateKey(),
            $this->getPublicKey(),
        );

        return $this->configuration;
    }

    public function getPrivateKey(): Key
    {
        $apiKeyFilepath = $this->parameterBag->get(static::FRONTEND_API_KEYS_FILEPATH_PARAMETER);

        return Key\InMemory::file($apiKeyFilepath . '/private.key');
    }

    public function getPublicKey(): Key
    {
        $apiKeyFilepath = $this->parameterBag->get(static::FRONTEND_API_KEYS_FILEPATH_PARAMETER);

        return Key\InMemory::file($apiKeyFilepath . '/public.key');
    }

    public function getSigner(): Signer
    {
        return new Signer\Rsa\Sha256();
    }
}
