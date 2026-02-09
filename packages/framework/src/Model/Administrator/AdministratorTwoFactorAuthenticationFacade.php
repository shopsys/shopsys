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
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CodeGeneratorInterface $emailCodeGenerator,
        protected readonly GoogleAuthenticatorInterface $googleAuthenticator,
        protected readonly PngWriter $pngWriter,
    ) {
    }

    public function enableTwoFactorAuthenticationByEmail(Administrator $administrator): void
    {
        $administrator->enableEmailAuth();
        $this->em->flush();
    }

    public function enableTwoFactorAuthenticationByGoogleAuthenticator(Administrator $administrator): void
    {
        $administrator->enableGoogleAuthenticator();
        $this->em->flush();
    }

    public function disableTwoFactorAuthentication(Administrator $administrator): void
    {
        $administrator->disableTwoFactorAuth();
        $this->em->flush();
    }

    public function renewGoogleAuthSecret(Administrator $administrator): void
    {
        $administrator->setGoogleAuthenticatorSecret($this->googleAuthenticator->generateSecret());
        $this->em->flush();
    }

    public function getQrCodeDataUri(Administrator $administrator): string
    {
        $qrCodeContent = $this->googleAuthenticator->getQRContent($administrator);

        $result = new Builder(
            writer: $this->pngWriter,
            writerOptions: [],
            data: $qrCodeContent,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 250,
            margin: 30,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        return 'data:image/png;base64,' . base64_encode($result->build()->getString());
    }

    public function isGoogleAuthenticatorCodeValid(Administrator $administrator, string $code): bool
    {
        return $this->googleAuthenticator->checkCode($administrator, $code);
    }

    public function generateAndSendEmail(Administrator $administrator): void
    {
        $this->emailCodeGenerator->generateAndSend($administrator);
    }
}
