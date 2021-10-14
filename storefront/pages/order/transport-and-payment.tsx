import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import { FC } from 'react';
import Form from 'components/Forms/Form';
import { getTransportAndPaymentFormResolver } from 'components/Pages/Order/TransportAndPayment/TransportAndPaymentFormResolver';
import { getTransports } from 'connectors/transports/Transports';
import { navigationQuery } from 'connectors/navigation/Navigation';
import OrderLayout from 'components/Layout/OrderLayout';
import Select from 'components/Pages/Order/TransportAndPayment/Select';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useInitDomainConfig } from 'hooks/helpers/UseInitDomainConfig';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const TransportAndPayment: FC<ServerSidePropsType> = (props) => {
    useInitDomainConfig(props.domainConfig);
    const { cartUuid, transport, payment } = useShopsysSelector((state) => state.cartInput);
    const transports = getTransports(cartUuid);
    const transportObject = useShopsysSelector((state) => state.user.transport);
    const t = useTypedTranslationFunction();

    return (
        <StaticUrlGuard domainUrl={props.domainConfig.url}>
            <Form
                defaultValues={{
                    transport: transport === null ? null : transport.uuid,
                    personalPickupStore:
                        transport?.personalPickupStoreUuid === undefined ? null : transport.personalPickupStoreUuid,
                    payment: payment === null ? null : payment.uuid,
                }}
                resolver={getTransportAndPaymentFormResolver(
                    transportObject === null ? false : transportObject.hasPersonalPickup,
                )}
            >
                <OrderLayout activeStep={2} buttonNextText={t('Contact information')}>
                    {transports.length > 0 && <Select transports={transports} />}
                </OrderLayout>
            </Form>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    return initServerSideProps(context, store, [navigationQuery]);
});

export default TransportAndPayment;
