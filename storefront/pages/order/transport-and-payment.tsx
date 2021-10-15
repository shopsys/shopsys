import { FC, useEffect, useState } from 'react';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import { TransportInputType, TransportType } from 'connectors/transports/types';
import { FormProvider } from 'react-hook-form';
import { getTransports } from 'connectors/transports/Transports';
import { navigationQuery } from 'connectors/navigation/Navigation';
import OrderAction from 'components/Blocks/OrderAction';
import OrderLayout from 'components/Layout/OrderLayout';
import { PaymentInputType } from 'connectors/payments/types';
import Select from 'components/Pages/Order/TransportAndPayment/Select';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useInitDomainConfig } from 'hooks/helpers/UseInitDomainConfig';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const TransportAndPayment: FC<ServerSidePropsType> = (props) => {
    useInitDomainConfig(props.domainConfig);
    const { cartUuid, transport, payment } = useShopsysSelector((state) => state.cartInput);
    const { url } = useShopsysSelector((state) => state.domain);
    const [cartUrl, contactInformationUrl] = useGetInternationalizedStaticUrls(
        ['/cart', '/order/contact-information'],
        url,
    );
    const transports = getTransports(cartUuid);
    const transportObject = useShopsysSelector((state) => state.user.transport);
    const [isTransportAndPaymentValid, setTransportAndPaymentValidity] = useState(
        getTransportAndPaymentValidity(transport, transportObject, payment),
    );
    const t = useTypedTranslationFunction();
    const formProviderMethods = useShopsysForm(undefined, {
        transport: transport === null ? null : transport.uuid,
        personalPickupStore:
            transport?.personalPickupStoreUuid === undefined ? null : transport.personalPickupStoreUuid,
        payment: payment === null ? null : payment.uuid,
    });

    useEffect(() => {
        setTransportAndPaymentValidity(getTransportAndPaymentValidity(transport, transportObject, payment));
    }, [transport, transportObject, payment]);

    return (
        <StaticUrlGuard domainUrl={props.domainConfig.url}>
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
    return initServerSideProps(context, store, [navigationQuery]);
});

const getTransportAndPaymentValidity = (
    transportInput: TransportInputType | null,
    transport: TransportType | null,
    paymentInput: PaymentInputType | null,
) => {
    if (transport?.hasPersonalPickup) {
        return transportInput !== null && transportInput?.personalPickupStoreUuid !== null && paymentInput !== null;
    }
    return transportInput !== null && paymentInput !== null;
};

export default TransportAndPayment;
