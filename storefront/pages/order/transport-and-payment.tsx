import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import Form from 'components/Forms/Form';
import { getTransports } from 'connectors/transports/Transports';
import { navigationQuery } from 'connectors/navigation/Navigation';
import OrderAction from 'components/Blocks/OrderAction';
import OrderSteps from 'components/Blocks/OrderSteps';
import OrderSummary from 'components/Blocks/OrderSummary';
import Select from 'components/Pages/Order/TransportAndPayment/Select';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useInitDomainConfig } from 'hooks/helpers/UseInitDomainConfig';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

const TransportAndPayment: FC<ServerSidePropsType> = (props) => {
    useInitDomainConfig(props.domainConfig);
    const { cartUuid, transport, payment } = useShopsysSelector((state) => state.cookie);
    const transports = getTransports(cartUuid);
    const t = useTypedTranslationFunction();

    return (
        <StaticUrlGuard domainUrl={props.domainConfig.url}>
            <CommonLayout>
                <OrderSteps activeStep={2} domainUrl={props.domainConfig.url} />
                <Form
                    defaultValues={{
                        transport: transport === null ? null : transport.uuid,
                        personalPickupStore:
                            transport?.personalPickupStoreUuid === undefined ? null : transport.personalPickupStoreUuid,
                        payment: payment === null ? null : payment.uuid,
                    }}
                >
                    {transports.length > 0 && (
                        <Webline>
                            <Select transports={transports} />
                            <OrderSummary />
                        </Webline>
                    )}
                    <Webline>
                        <OrderAction
                            activeStep={2}
                            buttonBack={t('Back')}
                            buttonNext={t('Contact information')}
                            isDisabled={false}
                            withGapTop={true}
                            withGapBottom={true}
                        />
                    </Webline>
                </Form>
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    return initServerSideProps(context, store, [navigationQuery]);
});

export default TransportAndPayment;
