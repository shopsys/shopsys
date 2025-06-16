import { useWatchdogFormMeta } from 'components/Blocks/Product/Watchdog/watchdogFormMeta';
import { useWatchdogForm } from 'components/Blocks/Product/Watchdog/watchdogFormMeta';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { Popup } from 'components/Layout/Popup/Popup';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { useCreateWatchdogMutation } from 'graphql/requests/watchDog/mutations/CreateWatchdogMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { onGtmCreateWatchdotEventHandler } from 'gtm/handlers/onGtmCreateWatchdotEventHandler';
import useTranslation from 'next-translate/useTranslation';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { useSessionStore } from 'store/useSessionStore';
import { WatchdogFormType } from 'types/form';
import { blurInput } from 'utils/forms/blurInput';
import { useScrollToFirstError } from 'utils/forms/useScrollToFirstError';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type WatchdogPopupProps = {
    productUuid: string;
};

export const WatchdogPopup: FC<WatchdogPopupProps> = ({ productUuid }) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const user = useCurrentCustomerData();
    const [, createWatchdog] = useCreateWatchdogMutation();

    const [formProviderMethods] = useWatchdogForm({
        email: user?.email ?? '',
        productUuid,
        gdprAgreement: false,
    });
    const formMeta = useWatchdogFormMeta(formProviderMethods);

    const watchdogHandler: SubmitHandler<WatchdogFormType> = async (watchdogFormData) => {
        blurInput();

        const createWatchdogResult = await createWatchdog({
            input: {
                email: watchdogFormData.email,
                productUuid: watchdogFormData.productUuid,
            },
        });

        updatePortalContent(null);

        if (createWatchdogResult.error !== undefined) {
            showErrorMessage(t('There was an error while creating your watchdog'), GtmMessageOriginType.watchdog);
            return;
        }

        showSuccessMessage(t('Your watchdog has been created'));

        onGtmCreateWatchdotEventHandler(watchdogFormData);
    };

    useScrollToFirstError(formMeta.formName, formProviderMethods);

    return (
        <Popup
            className="vl:w-auto w-11/12 overflow-x-auto lg:w-4/5"
            title={t('Watchdog')}
            ariaDescription={t(
                'This product is on watchdog. Please fill in your email below to be notified when the product becomes available.',
            )}
        >
            <FormProvider {...formProviderMethods}>
                <Form onSubmit={formProviderMethods.handleSubmit(watchdogHandler)}>
                    <FormContentWrapper>
                        <FormBlockWrapper>
                            <FormColumn>
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.email.name}
                                    render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                    textInputProps={{
                                        label: formMeta.fields.email.label,
                                        required: true,
                                        type: 'email',
                                        autoComplete: 'email',
                                    }}
                                />
                            </FormColumn>

                            <CheckboxControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.gdprAgreement.name}
                                render={(checkbox) => <ChoiceFormLine>{checkbox}</ChoiceFormLine>}
                                checkboxProps={{
                                    label: formMeta.fields.gdprAgreement.label,
                                    required: true,
                                }}
                            />

                            <FormButtonWrapper>
                                <SubmitButton aria-label={t('Submit form to send your watchdog')}>
                                    {t('Send')}
                                </SubmitButton>
                            </FormButtonWrapper>
                        </FormBlockWrapper>
                    </FormContentWrapper>
                </Form>
            </FormProvider>
        </Popup>
    );
};
