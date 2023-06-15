<?php

declare(strict_types=1);

namespace App\Model\Administrator;

use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Writer\PngWriter;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator\CodeGeneratorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\QrCode\QrCodeGenerator;

class AdministratorTwoFactorAuthenticationFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator\CodeGeneratorInterface $emailCodeGenerator
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface $googleAuthenticator
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\QrCode\QrCodeGenerator $qrCodeGenerator
     * @param \Endroid\QrCode\Writer\PngWriter $pngWriter
     */
    public function __construct(
        private EntityManagerInterface $em,
        private CodeGeneratorInterface $emailCodeGenerator,
        private GoogleAuthenticatorInterface $googleAuthenticator,
        private QrCodeGenerator $qrCodeGenerator,
        private PngWriter $pngWriter,
    ) {
    }

    /**
     * @param \App\Model\Administrator\Administrator $administrator
     */
    public function enableTwoFactorAuthenticationByEmail(Administrator $administrator): void
    {
        $administrator->enableEmailAuth();
        $this->em->flush();
    }

    /**
     * @param \App\Model\Administrator\Administrator $administrator
     */
    public function enableTwoFactorAuthenticationByGoogleAuthenticator(Administrator $administrator): void
    {
        $administrator->enableGoogleAuthenticator();
        $this->em->flush();
    }

    /**
     * @param \App\Model\Administrator\Administrator $administrator
     */
    public function disableTwoFactorAuthentication(Administrator $administrator): void
    {
        $administrator->disableTwoFactorAuth();
        $this->em->flush();
    }

    /**
     * @param \App\Model\Administrator\Administrator $administrator
     */
    public function renewGoogleAuthSecret(Administrator $administrator): void
    {
        $administrator->setGoogleAuthenticatorSecret($this->googleAuthenticator->generateSecret());
        $this->em->flush();
    }

    /**
     * @param \App\Model\Administrator\Administrator $administrator
     * @return string
     */
    public function getQrCodeDataUri(Administrator $administrator): string
    {
        return $this->pngWriter->writeDataUri($this->qrCodeGenerator->getGoogleAuthenticatorQrCode($administrator));
    }

    /**
     * @param \App\Model\Administrator\Administrator $administrator
     * @param string $code
     * @return bool
     */
    public function isGoogleAuthenticatorCodeValid(Administrator $administrator, string $code): bool
    {
        return $this->googleAuthenticator->checkCode($administrator, $code);
    }

    /**
     * @param \App\Model\Administrator\Administrator $administrator
     */
    public function generateAndSendEmail(Administrator $administrator): void
    {
        $this->emailCodeGenerator->generateAndSend($administrator);
    }
}
