import { FC, useEffect, useState } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import { TransportInputType, TransportType } from 'connectors/transports/types';
import ErrorPopup from 'components/Forms/Lib/ErrorPopup';
import { getTransports } from 'connectors/transports/Transports';
import { handleOrderPagesRedirect } from 'helpers/HandleOrderPagesRedirect';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { NavigationQueryDocumentApi } from 'graphql/generated';
import OrderAction from 'components/Blocks/OrderAction';
import OrderLayout from 'components/Layout/OrderLayout';
import { PaymentInputType } from 'connectors/payments/types';
import Select from 'components/Pages/Order/TransportAndPayment/Select';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useHandleErrorPopupVisibility } from 'hooks/forms/UseHandleErrorPopupVisibility';
import { useRouter } from 'next/router';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const TransportAndPayment: FC<ServerSidePropsType> = () => {
    const router = useRouter();
    const { cartUuid, transport, payment } = useShopsysSelector((state) => state.cartInput);
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [cartUrl, contactInformationUrl] = useGetInternationalizedStaticUrls(
        ['/cart', '/order/contact-information'],
        domainUrl,
    );
    const transports = getTransports(cartUuid);
    const transportObject = useShopsysSelector((state) => state.user.transport);
    const [isTransportAndPaymentValid, setTransportAndPaymentValidity] = useState(
        getTransportAndPaymentValidity(transport, transportObject, payment),
    );
    const t = useTypedTranslationFunction();
    const formProviderMethods = useShopsysForm(undefined, {
        transport: transport === null ? null : transport.uuid,
        payment: payment === null ? null : payment.uuid,
    });
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(
        formProviderMethods,
        !isTransportAndPaymentValid,
    );

    useEffect(() => {
        setTransportAndPaymentValidity(getTransportAndPaymentValidity(transport, transportObject, payment));
    }, [transport, transportObject, payment]);

    const onSelectTransportAndPaymentHandler: SubmitHandler<{ transport: string; payment: string }> = () => {
        event?.preventDefault();
        router.push(contactInformationUrl);
    };

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <form onSubmit={formProviderMethods.handleSubmit(onSelectTransportAndPaymentHandler)}>
                <FormProvider {...formProviderMethods}>
                    <OrderLayout activeStep={2} buttonNextText={t('Contact information')}>
                        {transports.length > 0 && <Select transports={transports} />}
                        <OrderAction
                            activeStep={2}
                            buttonBack={t('Back')}
                            buttonNext={t('Contact information')}
                            hasDisabledLook={!isTransportAndPaymentValid}
                            withGapTop={true}
                            withGapBottom={true}
                            buttonBackLink={cartUrl}
                        />
                    </OrderLayout>
                </FormProvider>
            </form>{' '}
            <ErrorPopup
                isVisible={isErrorPopupVisible}
                onCloseCallback={() => setErrorPopupVisibility(false)}
                errors={[
                    {
                        label: t('Transport and payment'),
                        message: t('You have to have both transport and payment selected.'),
                    },
                ]}
            />
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    const redirect = handleOrderPagesRedirect(context);
    return redirect === false ? initServerSideProps(context, store, [NavigationQueryDocumentApi]) : redirect;
});

const getTransportAndPaymentValidity = (
    transportInput: TransportInputType | null,
    transport: TransportType | null,
    paymentInput: PaymentInputType | null,
) => {
    if (transport?.hasPersonalPickup) {
        return transportInput !== null && transportInput?.pickupPlaceIdentifier !== null && paymentInput !== null;
    }
    return transportInput !== null && paymentInput !== null;
};

export default TransportAndPayment;
