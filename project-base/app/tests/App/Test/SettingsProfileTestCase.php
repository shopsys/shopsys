<?php

declare(strict_types=1);

namespace Tests\App\Test;

use App\Component\Setting\SettingsProfileApplier;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

abstract class SettingsProfileTestCase extends ApplicationTestCase
{
    /**
     * @inject
     */
    protected SettingsProfileApplier $settingsProfileApplier;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        
        // Aplikuje profil nastavení podle environment proměnné
        $this->applySettingsProfile();
    }

    private function applySettingsProfile(): void
    {
        $profileName = $_ENV['TEST_SETTINGS_PROFILE'] ?? 'baseline';
        
        // Aplikuje profil pro první doménu (lze rozšířit pro více domén)
        $this->settingsProfileApplier->applyProfile($profileName, Domain::FIRST_DOMAIN_ID);
    }
    
    /**
     * Získá aktuální profil nastavení
     */
    protected function getCurrentSettingsProfile(): string
    {
        return $_ENV['TEST_SETTINGS_PROFILE'] ?? 'baseline';
    }

    /**
     * Zkontroluje, zda běží v konkrétním profilu
     */
    protected function isRunningInProfile(string $profileName): bool
    {
        return $this->getCurrentSettingsProfile() === $profileName;
    }
}