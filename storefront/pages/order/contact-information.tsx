import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import { GetServerSideProps } from 'next';
import { navigationQuery } from 'connectors/navigation/Navigation';
import OrderAction from 'components/Blocks/OrderAction';
import OrderSteps from 'components/Blocks/OrderSteps';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

const ContactInformation: FC<ServerSidePropsType> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <StaticUrlGuard domainUrl={props.domainConfig.url}>
            <CommonLayout>
                <OrderSteps activeStep={3} domainUrl={props.domainConfig.url} />
                Contact information - step 3
                <OrderAction activeStep={3} buttonBack={t('Back')} buttonNext={t('Submit order')} />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps: GetServerSideProps = async (context) => {
    return initServerSideProps(context, [navigationQuery]);
};

export default ContactInformation;
