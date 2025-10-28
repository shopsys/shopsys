import { useOrderWithdrawalForm, useOrderWithdrawalFormMeta } from './orderWithdrawalFormMeta';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { TextareaControlled } from 'components/Forms/Textarea/TextareaControlled';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeOrderWithdrawalDataFragment } from 'graphql/requests/orders/fragments/OrderWithdrawalDataFragment.generated';
import { useOrderWithdrawalRequestMutation } from 'graphql/requests/orders/mutations/OrderWithdrawalRequestMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { useRouter } from 'next/router';
import { useCallback } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { OrderWithdrawalFormType } from 'types/form';
import { handleFormErrors } from 'utils/forms/handleFormErrors';
import { useErrorPopup } from 'utils/forms/useErrorPopup';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type OrderWithdrawalContentProps = {
    order: TypeOrderWithdrawalDataFragment;
};

export const OrderWithdrawalContent: FC<OrderWithdrawalContentProps> = ({ order }) => {
    const { t } = useTranslation();
    const router = useRouter();
    const { url } = useDomainConfig();
    const [formProviderMethods] = useOrderWithdrawalForm(order);
    const formMeta = useOrderWithdrawalFormMeta(formProviderMethods);
    const [, orderWithdrawalRequest] = useOrderWithdrawalRequestMutation();

    useErrorPopup(formProviderMethods, formMeta.fields, undefined, GtmMessageOriginType.other);

    const onSubmitHandler = useCallback<SubmitHandler<OrderWithdrawalFormType>>(
        async (values) => {
            const { firstName, lastName, email, telephone, note } = values;
            const result = await orderWithdrawalRequest({
                input: {
                    orderUrlHash: order.urlHash,
                    firstName,
                    lastName,
                    email,
                    telephone: telephone || null,
                    note: note || null,
                },
            });

            if (result.data?.OrderWithdrawalRequest) {
                const [orderWithdrawalSuccessUrl] = getInternationalizedStaticUrls(
                    [{ url: '/order-withdrawal-success/:orderUrlHash', param: order.urlHash }],
                    url,
                );
                router.push(orderWithdrawalSuccessUrl);
            }

            handleFormErrors(result.error, formProviderMethods, t, formMeta.messages.error);
        },
        [orderWithdrawalRequest, order.urlHash, formProviderMethods, t, formMeta.messages.error, router],
    );

    return (
        <Webline width="lg">
            <VerticalStack gap="sm">
                <h1>{t('Withdrawal from contract')}</h1>

                <p>
                    {t('Order number')}: <strong>{order.number}</strong>
                </p>

                <FormProvider {...formProviderMethods}>
                    <Form onSubmit={formProviderMethods.handleSubmit(onSubmitHandler)}>
                        <FormContentWrapper>
                            <FormBlockWrapper>
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.firstName.name}
                                    render={(textInput) => <FormLine>{textInput}</FormLine>}
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
                                    render={(textInput) => <FormLine>{textInput}</FormLine>}
                                    textInputProps={{
                                        label: formMeta.fields.lastName.label,
                                        required: true,
                                        type: 'text',
                                        autoComplete: 'family-name',
                                    }}
                                />
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.email.name}
                                    render={(textInput) => <FormLine>{textInput}</FormLine>}
                                    textInputProps={{
                                        label: formMeta.fields.email.label,
                                        required: true,
                                        type: 'email',
                                        autoComplete: 'email',
                                    }}
                                />
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.telephone.name}
                                    render={(textInput) => <FormLine>{textInput}</FormLine>}
                                    textInputProps={{
                                        label: formMeta.fields.telephone.label,
                                        required: false,
                                        type: 'tel',
                                        autoComplete: 'tel',
                                    }}
                                />
                                <TextareaControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.note.name}
                                    render={(textarea) => <FormLine>{textarea}</FormLine>}
                                    textareaProps={{
                                        label: formMeta.fields.note.label,
                                        required: false,
                                        rows: 4,
                                    }}
                                />
                                <FormButtonWrapper>
                                    <SubmitButton
                                        hasDisabledCursor={!formProviderMethods.formState.isValid}
                                        aria-label={t('Submit withdrawal request for order {{ orderNumber }}', {
                                            ns: 'accessibility',
                                            orderNumber: order.number,
                                        })}
                                    >
                                        {t('Confirm withdrawal from contract')}
                                    </SubmitButton>
                                </FormButtonWrapper>
                            </FormBlockWrapper>
                        </FormContentWrapper>
                    </Form>
                </FormProvider>
            </VerticalStack>
        </Webline>
    );
};
