<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator\CodeGeneratorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;

class AdministratorTwoFactorAuthenticationFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator\CodeGeneratorInterface $emailCodeGenerator
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface $googleAuthenticator
     * @param \Endroid\QrCode\Writer\PngWriter $pngWriter
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CodeGeneratorInterface $emailCodeGenerator,
        protected readonly GoogleAuthenticatorInterface $googleAuthenticator,
        protected readonly PngWriter $pngWriter,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     */
    public function enableTwoFactorAuthenticationByEmail(Administrator $administrator): void
    {
        $administrator->enableEmailAuth();
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     */
    public function enableTwoFactorAuthenticationByGoogleAuthenticator(Administrator $administrator): void
    {
        $administrator->enableGoogleAuthenticator();
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     */
    public function disableTwoFactorAuthentication(Administrator $administrator): void
    {
        $administrator->disableTwoFactorAuth();
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     */
    public function renewGoogleAuthSecret(Administrator $administrator): void
    {
        $administrator->setGoogleAuthenticatorSecret($this->googleAuthenticator->generateSecret());
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @return string
     */
    public function getQrCodeDataUri(Administrator $administrator): string
    {
        $qrCodeContent = $this->googleAuthenticator->getQRContent($administrator);

        $result = Builder::create()
            ->writer($this->pngWriter)
            ->writerOptions([])
            ->data($qrCodeContent)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(250)
            ->margin(30)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();

        return 'data:image/png;base64,' . base64_encode($result->getString());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @param string $code
     * @return bool
     */
    public function isGoogleAuthenticatorCodeValid(Administrator $administrator, string $code): bool
    {
        return $this->googleAuthenticator->checkCode($administrator, $code);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     */
    public function generateAndSendEmail(Administrator $administrator): void
    {
        $this->emailCodeGenerator->generateAndSend($administrator);
    }
}
