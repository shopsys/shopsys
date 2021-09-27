import { getTransports, transportsQuery } from 'connectors/transports/Transports';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import Form from 'components/Forms/Form';
import { GetServerSideProps } from 'next';
import { navigationQuery } from 'connectors/navigation/Navigation';
import OrderAction from 'components/Blocks/OrderAction';
import OrderSteps from 'components/Blocks/OrderSteps';
import Select from 'components/Pages/Order/TransportAndPayment/Select';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useInitDomainConfig } from 'hooks/helpers/UseInitDomainConfig';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

const TransportAndPayment: FC<ServerSidePropsType> = (props) => {
    const transports = getTransports();
    const t = useTypedTranslationFunction();
    useInitDomainConfig(props.domainConfig);

    return (
        <StaticUrlGuard domainUrl={props.domainConfig.url}>
            <CommonLayout>
                <OrderSteps activeStep={2} domainUrl={props.domainConfig.url} />
                <Form
                    defaultValues={{ transport: undefined, payment: undefined }}
                    onSubmitHandler={() => console.log('submitted')}
                    onSuccessHandler={() => console.log('success')}
                >
                    <Webline>
                        <Select transports={transports} />
                    </Webline>
                    <OrderAction activeStep={2} buttonBack={t('Back')} buttonNext={t('Contact information')} />
                </Form>
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps: GetServerSideProps = async (context) => {
    return initServerSideProps(context, [navigationQuery, transportsQuery]);
};

export default TransportAndPayment;
