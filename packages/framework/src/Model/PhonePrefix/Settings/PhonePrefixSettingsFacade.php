<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PhonePrefix\Settings;

use Doctrine\ORM\EntityManagerInterface;

class PhonePrefixSettingsFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly PhonePrefixRepository $phonePrefixRepository,
        protected readonly PhonePrefixFactory $phonePrefixFactory,
        protected readonly PhonePrefixSettingsDataFactory $phonePrefixSettingsDataFactory,
    ) {
    }

    public function getByDomainId(int $domainId): PhonePrefixSettingsData
    {
        $phonePrefixSettingsData = $this->phonePrefixSettingsDataFactory->create();

        foreach ($this->phonePrefixRepository->findAllByDomainId($domainId) as $phonePrefixSetting) {
            $phonePrefixSettingsData->enabledCodes[] = $phonePrefixSetting->getCode();

            if ($phonePrefixSetting->isDefault()) {
                $phonePrefixSettingsData->defaultCode = $phonePrefixSetting->getCode();
            }
        }

        return $phonePrefixSettingsData;
    }

    public function edit(PhonePrefixSettingsData $phonePrefixSettingsData, int $domainId): void
    {
        $this->phonePrefixRepository->deleteAllByDomainId($domainId);

        foreach ($phonePrefixSettingsData->enabledCodes as $code) {
            $isDefault = $phonePrefixSettingsData->defaultCode !== null
                && $code === $phonePrefixSettingsData->defaultCode;

            $this->em->persist($this->phonePrefixFactory->create($domainId, $code, $isDefault));
        }

        $this->em->flush();
    }

    /**
     * @param int[] $domainIds
     * @return int[]
     */
    public function filterOutConfiguredDomainIds(array $domainIds): array
    {
        return $this->phonePrefixRepository->filterOutConfiguredDomainIds($domainIds);
    }
}
