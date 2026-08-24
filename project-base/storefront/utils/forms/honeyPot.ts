import { UseFormReturn } from 'react-hook-form';

/**
 * It has to match Shopsys\FrontendApiBundle\Model\SpamProtection\FormSpamProtectionFacade::HONEY_POT_FIELD_NAME.
 */
export const HONEY_POT_FIELD_NAME = 'subject';

export const useHoneyPot = (formProviderMethods: UseFormReturn<any>) => ({
    renderHoneyPot: true as const,
    getHoneyPotInput: () => ({
        [HONEY_POT_FIELD_NAME]: formProviderMethods.getValues(HONEY_POT_FIELD_NAME) ?? null,
    }),
});
