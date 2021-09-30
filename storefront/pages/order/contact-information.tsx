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

const ContactInformation: FC<ServerSidePropsType> = (props) => {
    useInitDomainConfig(props.domainConfig);
    const t = useTypedTranslationFunction();
    const cart = useShopsysSelector((state) => state.user.cart);

    return (
        <StaticUrlGuard domainUrl={props.domainConfig.url}>
            <CommonLayout>
                <OrderSteps activeStep={3} domainUrl={props.domainConfig.url} />
                Contact information - step 3
                <OrderAction activeStep={3} buttonBack={t('Back')} buttonNext={t('Submit order')} />
                {cart === undefined ? null : <OrderSummary cart={cart} />}
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps: GetServerSideProps = async (context) => {
    return initServerSideProps(context, [navigationQuery]);
};

export default ContactInformation;
