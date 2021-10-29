import { FC, useEffect, useState } from 'react';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import { TransportInputType, TransportType } from 'connectors/transports/types';
import { FormProvider } from 'react-hook-form';
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
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const TransportAndPayment: FC<ServerSidePropsType> = () => {
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
        personalPickupStore: transport?.pickupPlaceIdentifier === undefined ? null : transport.pickupPlaceIdentifier,
        payment: payment === null ? null : payment.uuid,
    });

    useEffect(() => {
        setTransportAndPaymentValidity(getTransportAndPaymentValidity(transport, transportObject, payment));
    }, [transport, transportObject, payment]);

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <FormProvider {...formProviderMethods}>
                <OrderLayout activeStep={2} buttonNextText={t('Contact information')}>
                    {transports.length > 0 && <Select transports={transports} />}
                    <OrderAction
                        activeStep={2}
                        buttonBack={t('Back')}
                        buttonNext={t('Contact information')}
                        isDisabled={!isTransportAndPaymentValid}
                        withGapTop={true}
                        withGapBottom={true}
                        buttonBackLink={cartUrl}
                        buttonNextLink={contactInformationUrl}
                    />
                </OrderLayout>
            </FormProvider>
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
