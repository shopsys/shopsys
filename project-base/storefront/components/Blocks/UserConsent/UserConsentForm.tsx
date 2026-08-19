import { Button } from 'components/Forms/Button/Button';
import { ToggleSwitchControlled } from 'components/Forms/ToggleSwitch/ToggleSwitchControlled';
import { onGtmConsentUpdateEventHandler } from 'gtm/handlers/onGtmConsentUpdateEventHandler';
import { getGtmConsentInfo } from 'gtm/utils/getGtmConsentInfo';
import { JSX, ReactElement } from 'react';
import { FormProvider } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { UserConsentFormType } from 'types/form';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import { useUserConsentForm, useUserConsentFormMeta } from './userConsentFormMeta';

type UserConsentFormProps = {
    layout?: 'compact' | 'default';
    onSetCallback?: () => void;
};

export const UserConsentForm: FC<UserConsentFormProps> = ({ layout = 'default', onSetCallback }) => {
    const { t } = useTranslation();
    const [formProviderMethods] = useUserConsentForm();
    const formMeta = useUserConsentFormMeta();
    const updateUserConsent = usePersistStore((store) => store.updateUserConsent);
    const isCompact = layout === 'compact';

    const saveUserConsentChoices = () => {
        const formValues = formProviderMethods.getValues();
        updateUserConsent(formValues);
        onGtmConsentUpdateEventHandler(getGtmConsentInfo(formValues));

        if (onSetCallback) {
            onSetCallback();
        }
    };

    const giveFullUserConsent = () => {
        for (const key in formMeta.fields) {
            formProviderMethods.setValue(key as keyof UserConsentFormType, true, { shouldValidate: true });
        }

        saveUserConsentChoices();
    };

    const rejectUserConsent = () => {
        for (const key in formMeta.fields) {
            formProviderMethods.setValue(key as keyof UserConsentFormType, false, { shouldValidate: true });
        }

        saveUserConsentChoices();
    };

    return (
        <FormProvider {...formProviderMethods}>
            <div className={twMergeCustom('flex flex-col gap-5', isCompact ? 'gap-4' : 'mx-auto w-full max-w-2xl')}>
                <div className="divide-y divide-border-less overflow-hidden rounded-xl border border-border-less bg-background-more">
                    <ToggleSwitchControlled
                        ariaLabel={t('Toggle marketing consent', { ns: 'accessibility' })}
                        control={formProviderMethods.control}
                        formName={formMeta.formName}
                        name={formMeta.fields.marketing.name}
                        render={(toggleSwitch, toggleSwitchId) => (
                            <ToggleContent
                                isCompact={isCompact}
                                name={formMeta.fields.marketing.label}
                                toggleSwitch={toggleSwitch}
                                toggleSwitchId={toggleSwitchId}
                            />
                        )}
                    />

                    <ToggleSwitchControlled
                        ariaLabel={t('Toggle statistics consent', { ns: 'accessibility' })}
                        control={formProviderMethods.control}
                        formName={formMeta.formName}
                        name={formMeta.fields.statistics.name}
                        render={(toggleSwitch, toggleSwitchId) => (
                            <ToggleContent
                                isCompact={isCompact}
                                name={formMeta.fields.statistics.label}
                                toggleSwitch={toggleSwitch}
                                toggleSwitchId={toggleSwitchId}
                            />
                        )}
                    />

                    <ToggleSwitchControlled
                        ariaLabel={t('Toggle preferences consent', { ns: 'accessibility' })}
                        control={formProviderMethods.control}
                        formName={formMeta.formName}
                        name={formMeta.fields.preferences.name}
                        render={(toggleSwitch, toggleSwitchId) => (
                            <ToggleContent
                                isCompact={isCompact}
                                name={formMeta.fields.preferences.label}
                                toggleSwitch={toggleSwitch}
                                toggleSwitchId={toggleSwitchId}
                            />
                        )}
                    />
                </div>

                <div className="flex flex-col-reverse gap-2.5 sm:flex-row sm:justify-end">
                    <Button className="w-full sm:w-auto" size="small" variant="tertiary" onClick={rejectUserConsent}>
                        {t('Reject all')}
                    </Button>

                    <Button
                        className="w-full sm:w-auto"
                        size="small"
                        variant="secondary"
                        onClick={saveUserConsentChoices}
                    >
                        {t('Save choices')}
                    </Button>

                    <Button className="w-full sm:w-auto" size="small" onClick={giveFullUserConsent}>
                        {t('Accept all')}
                    </Button>
                </div>
            </div>
        </FormProvider>
    );
};

type ToggleContentProps = {
    isCompact: boolean;
    name: string | ReactElement;
    toggleSwitch: JSX.Element;
    toggleSwitchId: string;
};

const ToggleContent: FC<ToggleContentProps> = ({ isCompact, name, toggleSwitch, toggleSwitchId }) => (
    <label
        className={twMergeCustom(
            'flex cursor-pointer items-center justify-between gap-4 px-4 font-secondary font-semibold text-sm transition-colors hover:bg-background-default',
            isCompact ? 'min-h-13 py-2.5' : 'min-h-16 py-3',
        )}
        htmlFor={toggleSwitchId}
    >
        <span className="text-text-default">{name}</span>
        {toggleSwitch}
    </label>
);
