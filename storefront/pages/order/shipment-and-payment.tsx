import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import { GetServerSideProps } from 'next';
import { navigationQuery } from 'connectors/navigation/Navigation';
import OrderAction from 'components/Blocks/OrderAction';
import OrderSteps from 'components/Blocks/OrderSteps';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

const ShipmentAndPayment: FC<ServerSidePropsType> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <StaticUrlGuard domainUrl={props.domainConfig.url}>
            <CommonLayout>
                <OrderSteps activeStep={2} domainUrl={props.domainConfig.url} />
                Shipment and payment - step 2
                <OrderAction activeStep={2} buttonBack={t('Back')} buttonNext={t('Contact information')} />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps: GetServerSideProps = async (context) => {
    return initServerSideProps(context, [navigationQuery]);
};

export default ShipmentAndPayment;
