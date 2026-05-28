import { useWatchdogForm, useWatchdogFormMeta } from 'components/Blocks/Product/Watchdog/watchdogFormMeta';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { Popup } from 'components/Layout/Popup/Popup';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { useCreateWatchdogMutation } from 'graphql/requests/watchDog/mutations/CreateWatchdogMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { onGtmCreateWatchdotEventHandler } from 'gtm/handlers/onGtmCreateWatchdotEventHandler';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { useSessionStore } from 'store/useSessionStore';
import { WatchdogFormType } from 'types/form';
import { WatchDogProductType } from 'types/product';
import { useErrorHandler } from 'utils/errors/useErrorHandler';
import { blurInput } from 'utils/forms/blurInput';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type WatchdogPopupProps = {
    product: WatchDogProductType;
    listIndex?: number;
};

export const WatchdogPopup: FC<WatchdogPopupProps> = ({ product, listIndex }) => {
    const { t } = useTranslation();
    const closePortalContent = useSessionStore((s) => s.closePortalContent);
    const user = useCurrentCustomerData();
    const [, createWatchdog] = useCreateWatchdogMutation();
    const domainConfig = useDomainConfig();
    const { canSeePrices } = useAuthorization();

    const [formProviderMethods] = useWatchdogForm({
        email: user?.email ?? '',
        productUuid: product.uuid,
        gdprAgreement: false,
    });
    const formMeta = useWatchdogFormMeta();
    const handleError = useErrorHandler({
        form: formProviderMethods,
        gtmOrigin: GtmMessageOriginType.watchdog,
        customMessage: t('There was an error while creating your watchdog'),
    });

    const watchdogHandler: SubmitHandler<WatchdogFormType> = async (watchdogFormData) => {
        blurInput();

        const createWatchdogResult = await createWatchdog({
            input: {
                email: watchdogFormData.email,
                productUuid: watchdogFormData.productUuid,
            },
        });

        if (createWatchdogResult.error !== undefined) {
            handleError(createWatchdogResult.error);
            return;
        }

        closePortalContent();
        showSuccessMessage(t('Your watchdog has been created'));

        onGtmCreateWatchdotEventHandler(watchdogFormData, product, domainConfig, !canSeePrices, listIndex);
    };

    return (
        <Popup
            className="vl:w-auto w-11/12 overflow-x-auto lg:w-4/5"
            title={t('Watchdog')}
            ariaDescription={t(
                'This product is on watchdog. Please fill in your email below to be notified when the product becomes available.',
                { ns: 'accessibility' },
            )}
        >
            <FormProvider {...formProviderMethods}>
                <Form formName={formMeta.formName} onSubmit={formProviderMethods.handleSubmit(watchdogHandler)}>
                    <FormContentWrapper>
                        <FormBlockWrapper>
                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.email.name}
                                textInputProps={{
                                    label: formMeta.fields.email.label,
                                    required: true,
                                    type: 'email',
                                    autoComplete: 'email',
                                }}
                            />

                            <CheckboxControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.gdprAgreement.name}
                                checkboxProps={{
                                    label: formMeta.fields.gdprAgreement.label,
                                    required: true,
                                }}
                            />
                        </FormBlockWrapper>

                        <FormButtonWrapper>
                            <SubmitButton aria-label={t('Send. Submit watchdog request', { ns: 'accessibility' })}>
                                {t('Send')}
                            </SubmitButton>
                        </FormButtonWrapper>
                    </FormContentWrapper>
                </Form>
            </FormProvider>
        </Popup>
    );
};
