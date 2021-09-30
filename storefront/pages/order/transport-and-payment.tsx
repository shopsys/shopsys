import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import Form from 'components/Forms/Form';
import { GetServerSideProps } from 'next';
import { getTransports } from 'connectors/transports/Transports';
import { getUserDataCookie } from 'helpers/Cookies';
import { navigationQuery } from 'connectors/navigation/Navigation';
import OrderAction from 'components/Blocks/OrderAction';
import OrderSteps from 'components/Blocks/OrderSteps';
import Select from 'components/Pages/Order/TransportAndPayment/Select';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useInitDomainConfig } from 'hooks/helpers/UseInitDomainConfig';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

const TransportAndPayment: FC<ServerSidePropsType> = (props) => {
    useInitDomainConfig(props.domainConfig);
    const transports = getTransports();
    const t = useTypedTranslationFunction();
    const userData = getUserDataCookie();

    return (
        <StaticUrlGuard domainUrl={props.domainConfig.url}>
            <CommonLayout>
                <OrderSteps activeStep={2} domainUrl={props.domainConfig.url} />
                <Form
                    defaultValues={{
                        transport: undefined,
                        personalPickup: undefined,
                        payment: undefined,
                    }}
                >
                    {transports.length > 0 && (
                        <Webline>
                            <Select transports={transports} {...userData} />
                        </Webline>
                    )}
                    <OrderAction activeStep={2} buttonBack={t('Back')} buttonNext={t('Contact information')} />
                </Form>
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps: GetServerSideProps = async (context) => {
    return initServerSideProps(context, [navigationQuery]);
};

export default TransportAndPayment;
