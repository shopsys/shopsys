import { contactInformationActions, ContactInformationFormType } from 'redux/slices/contactInformation';
import { FormProvider, SubmitHandler, useWatch } from 'react-hook-form';
import { initCartInputCookie, updateCartInputCookie } from 'helpers/Cookies';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { NavigationQueryDocumentApi, useCreateOrderMutationApi } from 'graphql/generated';
import { nextReduxWrapper, useShopsysDispatch, useShopsysSelector } from 'redux/main';
import ContactInformationForm from 'components/Pages/Order/ContactInformation';
import ErrorPopup from 'components/Forms/Lib/ErrorPopup';
import { FC } from 'react';
import Footer from 'components/Layout/Footer';
import Form from 'components/Forms/Form';
import { getContactInformationFormResolver } from 'components/Pages/Order/ContactInformation/ContactInformationFormResolver';
import { handleOrderPagesRedirect } from 'helpers/HandleOrderPagesRedirect';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import OrderAction from 'components/Blocks/OrderAction';
import OrderLayout from 'components/Layout/OrderLayout';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { updateCartState } from 'utils/Cart/UpdateCartState';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useHandleContactInformationNonTextChanges } from 'hooks/forms/useHandleContactInformationNonTextChanges';
import { useHandleErrorPopupVisibility } from 'hooks/forms/UseHandleErrorPopupVisibility';
import { useHandleFormErrors } from 'hooks/forms/UseHandleFormErrors';
import { useHandleFormSuccessfulSubmit } from 'hooks/forms/UseHandleFormSuccessfulSubmit';
import { userActions } from 'redux/slices/user';
import { useRouter } from 'next/router';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

const ContactInformation: FC<ServerSidePropsType> = () => {
    const router = useRouter();
    const dispatch = useShopsysDispatch();
    const contactInformationValues = useShopsysSelector((state) => state.contactInformation);
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [transportAndPaymentUrl, orderConfirmationUrl] = useGetInternationalizedStaticUrls(
        ['/order/transport-and-payment', '/order-confirmation'],
        domainUrl,
    );
    const cartInput = useShopsysSelector((state) => state.cartInput);
    const t = useTypedTranslationFunction();
    const [createOrderResult, createOrder] = useCreateOrderMutationApi();
    const formProviderMethods = useShopsysForm(getContactInformationFormResolver(t), contactInformationValues);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);
    useHandleFormSuccessfulSubmit(createOrderResult, formProviderMethods, contactInformationValues, () =>
        onSuccessfullyCreatedOrderHandler(),
    );
    useHandleFormErrors(createOrderResult.error, formProviderMethods, t('Could not create order'));
    useHandleContactInformationNonTextChanges(formProviderMethods.control, contactInformationValues);
    const isEmailValid = formProviderMethods.formState.errors.email === undefined;
    const differentDeliveryAddressValue = useWatch({
        control: formProviderMethods.control,
        name: 'differentDeliveryAddress',
    });
    const customerValue = useWatch({ control: formProviderMethods.control, name: 'customer' });
    const registerValue = useWatch({ control: formProviderMethods.control, name: 'register' });

    const onSuccessfullyCreatedOrderHandler = () => {
        const resetCartInput = initCartInputCookie();
        updateCartState(
            dispatch,
            { cart: null, transport: null, personalPickupStore: null, payment: null },
            resetCartInput,
        );
        updateCartInputCookie(resetCartInput);
        dispatch(userActions.setOrderConfirmationAccess(true));
        router.push(orderConfirmationUrl);
    };

    const onCreateOrderHandler: SubmitHandler<ContactInformationFormType> = async (formValues, event) => {
        event?.preventDefault();
        if (cartInput.transport === null || cartInput.payment === null) {
            router.replace(transportAndPaymentUrl);
            return;
        }

        dispatch(contactInformationActions.setContactInformation(formValues));

        await createOrder({
            ...formValues,
            ...{ ...cartInput, transport: cartInput.transport, payment: cartInput.payment },
            onCompanyBehalf: formValues.customer === 'companyCustomer',
            country: formValues.country.value,
            deliveryCountry: formValues.deliveryCountry.value,
        });
    };

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <FormProvider {...formProviderMethods}>
                <Form onSubmit={formProviderMethods.handleSubmit(onCreateOrderHandler)}>
                    <OrderLayout activeStep={3} buttonNextText={t('Submit order')}>
                        <ContactInformationForm />
                        <OrderAction
                            activeStep={3}
                            buttonBack={t('Back')}
                            buttonNext={t('Submit order')}
                            hasDisabledLook={!formProviderMethods.formState.isValid}
                            withGapTop={false}
                            withGapBottom={true}
                            buttonBackLink={transportAndPaymentUrl}
                        />
                    </OrderLayout>
                </Form>
            </FormProvider>
            <Webline type={'dark'}>
                <Footer />
            </Webline>
            <ErrorPopup
                isVisible={isErrorPopupVisible}
                onCloseCallback={() => setErrorPopupVisibility(false)}
                errors={(() => {
                    const errors = formProviderMethods.formState.errors;
                    const visibilitySettings = {
                        isFormVisible: isEmailValid,
                        isRegistrationVisible: registerValue,
                        isDeliveryAddressVisible: differentDeliveryAddressValue,
                        isCompanyVisible: customerValue === 'companyCustomer',
                    };

                    return [
                        {
                            label: t('Your e-mail'),
                            message: getErrorMessageByVisibility(errors.email?.message, visibilitySettings, {
                                isEmail: true,
                            }),
                        },
                        {
                            label: t('I want to register with an order'),
                            message: getErrorMessageByVisibility(errors.register?.message, visibilitySettings),
                        },
                        {
                            label: t('Password'),
                            message: getErrorMessageByVisibility(errors.passwordFirst?.message, visibilitySettings, {
                                isRegistration: true,
                            }),
                        },
                        {
                            label: t('Password again'),
                            message: getErrorMessageByVisibility(errors.passwordSecond?.message, visibilitySettings, {
                                isRegistration: true,
                            }),
                        },
                        {
                            label: t('You will shop with us like'),
                            message: getErrorMessageByVisibility(errors.customer?.message, visibilitySettings),
                        },
                        {
                            label: t('Telephone'),
                            message: getErrorMessageByVisibility(errors.telephone?.message, visibilitySettings),
                        },
                        {
                            label: t('First name'),
                            message: getErrorMessageByVisibility(errors.firstName?.message, visibilitySettings),
                        },
                        {
                            label: t('Last name'),
                            message: getErrorMessageByVisibility(errors.lastName?.message, visibilitySettings),
                        },
                        {
                            label: t('Company name'),
                            message: getErrorMessageByVisibility(errors.companyName?.message, visibilitySettings, {
                                isCompany: true,
                            }),
                        },
                        {
                            label: t('Company number'),
                            message: getErrorMessageByVisibility(errors.companyNumber?.message, visibilitySettings, {
                                isCompany: true,
                            }),
                        },
                        {
                            label: t('Tax number'),
                            message: getErrorMessageByVisibility(errors.companyTaxNumber?.message, visibilitySettings, {
                                isCompany: true,
                            }),
                        },
                        {
                            label: t('Street'),
                            message: getErrorMessageByVisibility(errors.street?.message, visibilitySettings),
                        },
                        {
                            label: t('City'),
                            message: getErrorMessageByVisibility(errors.city?.message, visibilitySettings),
                        },
                        {
                            label: t('Postcode'),
                            message: getErrorMessageByVisibility(errors.postcode?.message, visibilitySettings),
                        },
                        {
                            label: t('Enter the delivery address'),
                            message: getErrorMessageByVisibility(
                                errors.differentDeliveryAddress?.message,
                                visibilitySettings,
                            ),
                        },
                        {
                            label: t('First name'),
                            message: getErrorMessageByVisibility(
                                errors.deliveryFirstName?.message,
                                visibilitySettings,
                                { isDeliveryAddress: true },
                            ),
                        },
                        {
                            label: t('Last name'),
                            message: getErrorMessageByVisibility(errors.deliveryLastName?.message, visibilitySettings, {
                                isDeliveryAddress: true,
                            }),
                        },
                        {
                            label: t('Company'),
                            message: getErrorMessageByVisibility(
                                errors.deliveryCompanyName?.message,
                                visibilitySettings,
                                { isDeliveryAddress: true },
                            ),
                        },
                        {
                            label: t('Telephone'),
                            message: getErrorMessageByVisibility(
                                errors.deliveryTelephone?.message,
                                visibilitySettings,
                                { isDeliveryAddress: true },
                            ),
                        },
                        {
                            label: t('Street and house number'),
                            message: getErrorMessageByVisibility(errors.deliveryStreet?.message, visibilitySettings, {
                                isDeliveryAddress: true,
                            }),
                        },
                        {
                            label: t('City'),
                            message: getErrorMessageByVisibility(errors.deliveryCity?.message, visibilitySettings, {
                                isDeliveryAddress: true,
                            }),
                        },
                        {
                            label: t('Postcode'),
                            message: getErrorMessageByVisibility(errors.deliveryPostcode?.message, visibilitySettings, {
                                isDeliveryAddress: true,
                            }),
                        },
                        {
                            label: t('I want to subscribe to the newsletter'),
                            message: getErrorMessageByVisibility(
                                errors.newsletterSubscription?.message,
                                visibilitySettings,
                            ),
                        },
                    ];
                })()}
            />
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    const redirect = handleOrderPagesRedirect(context);
    return redirect === false ? initServerSideProps(context, store, [{ query: NavigationQueryDocumentApi }]) : redirect;
});

const getErrorMessageByVisibility = (
    message: string | undefined,
    visibility: {
        isFormVisible: boolean;
        isRegistrationVisible: boolean;
        isDeliveryAddressVisible: boolean;
        isCompanyVisible: boolean;
    },
    fieldInfo?: { isEmail?: boolean; isRegistration?: boolean; isDeliveryAddress?: boolean; isCompany?: boolean },
) => {
    if (fieldInfo?.isEmail === true) {
        return message;
    }
    if (fieldInfo?.isRegistration === true) {
        if (visibility.isRegistrationVisible) {
            return message;
        }
        return undefined;
    }
    if (fieldInfo?.isDeliveryAddress === true) {
        if (visibility.isDeliveryAddressVisible) {
            return message;
        }
        return undefined;
    }
    if (fieldInfo?.isCompany === true) {
        if (visibility.isCompanyVisible) {
            return message;
        }
        return undefined;
    }
    if (visibility.isFormVisible) {
        return message;
    }
    return undefined;
};

export default ContactInformation;
