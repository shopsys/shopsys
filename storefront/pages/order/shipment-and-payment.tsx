import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import { GetServerSideProps } from 'next';
import { navigationQuery } from 'connectors/navigation/Navigation';
import OrderAction from 'components/Blocks/OrderAction';
import OrderSteps from 'components/Blocks/OrderSteps';
import OrderSummary from 'components/Blocks/OrderSummary';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useInitDomainConfig } from 'hooks/helpers/UseInitDomainConfig';
import { useShopsysSelector } from 'redux/store';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ShipmentAndPayment: FC<ServerSidePropsType> = (props) => {
    const t = useTypedTranslationFunction();
    useInitDomainConfig(props.domainConfig);
    const cart = useShopsysSelector((state) => state.user.cart);

    return (
        <StaticUrlGuard domainUrl={props.domainConfig.url}>
            <CommonLayout>
                <OrderSteps activeStep={2} domainUrl={props.domainConfig.url} />
                Shipment and payment - step 2
                <OrderAction activeStep={2} buttonBack={t('Back')} buttonNext={t('Contact information')} />
                {cart === undefined ? null : <OrderSummary cart={cart} />}
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps: GetServerSideProps = async (context) => {
    return initServerSideProps(context, [navigationQuery]);
};

export default ShipmentAndPayment;
