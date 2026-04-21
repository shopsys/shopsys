import { DocumentDeleteIcon } from 'components/Basic/Icon/DocumentDeleteIcon';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { PhoneNumberInputControlled } from 'components/Forms/PhonePrefixSelect/PhoneNumberInputControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeOrderWithdrawalDataFragment } from 'graphql/requests/orders/fragments/OrderWithdrawalDataFragment.generated';
import { useOrderWithdrawalRequestMutation } from 'graphql/requests/orders/mutations/OrderWithdrawalRequestMutation.generated';
import { useRouter } from 'next/router';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { useSessionStore } from 'store/useSessionStore';
import { OrderWithdrawalFormType } from 'types/form';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useOrderWithdrawalForm, useOrderWithdrawalFormMeta } from './orderWithdrawalFormMeta';

type OrderWithdrawalContentProps = {
    order: TypeOrderWithdrawalDataFragment;
};

export const OrderWithdrawalContent: FC<OrderWithdrawalContentProps> = ({ order }) => {
    const { t } = useTranslation();
    const router = useRouter();
    const { url } = useDomainConfig();
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const [formProviderMethods] = useOrderWithdrawalForm(order);
    const formMeta = useOrderWithdrawalFormMeta();
    const [, orderWithdrawalRequest] = useOrderWithdrawalRequestMutation();

    const onSubmitHandler: SubmitHandler<OrderWithdrawalFormType> = async (values) => {
        const { firstName, lastName, email, note } = values;
        const result = await orderWithdrawalRequest({
            input: {
                orderUrlHash: order.urlHash,
                firstName,
                lastName,
                email,
                telephone: values.telephone
                    ? {
                          prefix: values.telephonePrefix,
                          countryCode: values.telephonePrefixCountryCode || '',
                          number: values.telephone,
                      }
                    : null,
                note: note || null,
            },
        });

        if (result.data?.OrderWithdrawalRequest) {
            const [orderWithdrawalSuccessUrl] = getInternationalizedStaticUrls(
                [{ url: '/order-withdrawal-success/:orderUrlHash', param: order.urlHash }],
                url,
            );
            updatePageLoadingState({ isPageLoading: true, redirectPageType: 'order-withdrawal-success' });
            router.replace(orderWithdrawalSuccessUrl);
        }
    };

    return (
        <Webline width="lg">
            <VerticalStack gap="sm">
                <PageHero
                    descriptionTid={TIDs.order_withdrawal_description}
                    icon={DocumentDeleteIcon}
                    title={t('Withdrawal from contract')}
                    description={t(
                        'Please fill in the following data to request a withdrawal from the order {{ orderNumber }}.',
                        {
                            orderNumber: order.number,
                        },
                    )}
                />

                <FormProvider {...formProviderMethods}>
                    <Form
                        formName={formMeta.formName}
                        tid={TIDs.order_withdrawal_form}
                        onSubmit={formProviderMethods.handleSubmit(onSubmitHandler)}
                    >
                        <FormContentWrapper>
                            <FormBlockWrapper>
                                <FormHeading>{t('Personal data')}</FormHeading>

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

                                <FormColumn>
                                    <TextInputControlled
                                        control={formProviderMethods.control}
                                        formName={formMeta.formName}
                                        name={formMeta.fields.firstName.name}
                                        width="half"
                                        textInputProps={{
                                            label: formMeta.fields.firstName.label,
                                            required: true,
                                            type: 'text',
                                            autoComplete: 'given-name',
                                        }}
                                    />

                                    <TextInputControlled
                                        control={formProviderMethods.control}
                                        formName={formMeta.formName}
                                        name={formMeta.fields.lastName.name}
                                        width="half"
                                        textInputProps={{
                                            label: formMeta.fields.lastName.label,
                                            required: true,
                                            type: 'text',
                                            autoComplete: 'family-name',
                                        }}
                                    />
                                </FormColumn>

                                <PhoneNumberInputControlled
                                    formName={formMeta.formName}
                                    formProviderMethods={formProviderMethods}
                                    isTelephoneRequired={false}
                                    prefixCountryCodeName={formMeta.fields.telephonePrefixCountryCode.name}
                                    prefixName={formMeta.fields.telephonePrefix.name}
                                    telephoneLabel={formMeta.fields.telephone.label}
                                    telephoneName={formMeta.fields.telephone.name}
                                />
                            </FormBlockWrapper>

                            <FormBlockWrapper>
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.note.name}
                                    textInputProps={{
                                        label: formMeta.fields.note.label,
                                        required: false,
                                        type: 'text',
                                        autoComplete: 'note',
                                    }}
                                />
                            </FormBlockWrapper>

                            <FormButtonWrapper>
                                <SubmitButton
                                    hasDisabledCursor={!formProviderMethods.formState.isValid}
                                    tid={TIDs.order_withdrawal_submit_button}
                                    aria-label={t('Submit withdrawal request for order {{ orderNumber }}', {
                                        ns: 'accessibility',
                                        orderNumber: order.number,
                                    })}
                                >
                                    {t('Confirm withdrawal from contract')}
                                </SubmitButton>
                            </FormButtonWrapper>
                        </FormContentWrapper>
                    </Form>
                </FormProvider>
            </VerticalStack>
        </Webline>
    );
};
