import { useWatchdogForm, useWatchdogFormMeta } from 'components/Blocks/Product/Watchdog/watchdogFormMeta';
import { Button } from 'components/Forms/Button/Button';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form, FormBlockWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { Popup } from 'components/Layout/Popup/Popup';
import { PopupActions } from 'components/Layout/Popup/PopupActions';
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
        customMessage: t("We couldn't set up the notification. Please try again."),
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
        showSuccessMessage(t("We'll email you when this product is in stock."));

        onGtmCreateWatchdotEventHandler(watchdogFormData, product, domainConfig, !canSeePrices, listIndex);
    };

    return (
        <Popup
            size="small"
            className="overflow-x-auto"
            title={t('Notify me when available')}
            ariaDescription={t("Enter your email and we'll let you know when this product is in stock.")}
        >
            <p aria-hidden="true" className="mb-4 text-sm">
                {t("Enter your email and we'll let you know when this product is in stock.")}
            </p>
            <FormProvider {...formProviderMethods}>
                <Form formName={formMeta.formName} onSubmit={formProviderMethods.handleSubmit(watchdogHandler)}>
                    <FormContentWrapper>
                        {/* the consent belongs to the email, so it sits closer to it than the fields are to each other */}
                        <FormBlockWrapper className="gap-3" variant="plain">
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

                        <PopupActions>
                            <Button className="w-full sm:w-auto" variant="outline" onClick={closePortalContent}>
                                {t('Cancel')}
                            </Button>

                            <SubmitButton className="w-full sm:w-auto" wrapperClassName="w-full sm:w-fit">
                                {t('Notify me')}
                            </SubmitButton>
                        </PopupActions>
                    </FormContentWrapper>
                </Form>
            </FormProvider>
        </Popup>
    );
};
