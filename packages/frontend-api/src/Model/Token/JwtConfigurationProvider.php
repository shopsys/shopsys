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

    /**
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly ParameterBagInterface $parameterBag,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return \Lcobucci\JWT\Configuration
     */
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

    /**
     * @return \Lcobucci\JWT\Signer\Key
     */
    public function getPrivateKey(): Key
    {
        $apiKeyFilepath = $this->parameterBag->get(static::FRONTEND_API_KEYS_FILEPATH_PARAMETER);

        return Key\InMemory::file($apiKeyFilepath . '/private.key');
    }

    /**
     * @return \Lcobucci\JWT\Signer\Key
     */
    public function getPublicKey(): Key
    {
        $apiKeyFilepath = $this->parameterBag->get(static::FRONTEND_API_KEYS_FILEPATH_PARAMETER);

        return Key\InMemory::file($apiKeyFilepath . '/public.key');
    }

    /**
     * @return \Lcobucci\JWT\Signer
     */
    public function getSigner(): Signer
    {
        return new Signer\Rsa\Sha256();
    }
}
