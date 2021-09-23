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

const transports = [
    {
        uuid: 'eb48e984-9936-4502-ad17-3bb6faaabef9',
        name: 'Česká pošta - balík do ruky',
        description: null,
        instruction: null,
        position: 0,
        price: {
            priceWithVat: 121,
            priceWithoutVat: 100,
            vatAmount: 21,
        },
        image: {
            url: 'http://127.0.0.1:8000/content/images/transport/default/56.jpg',
            width: 20,
            height: 20,
        },
        payments: [
            {
                description: null,
                instruction: null,
                uuid: '7ee17edb-0961-411a-ae66-49eac1f7fd8e',
                name: 'Dobírka',
                price: {
                    priceWithVat: 50,
                    priceWithoutVat: 50,
                    vatAmount: 0,
                },
                image: {
                    url: 'http://127.0.0.1:8000/content/images/payment/default/55.jpg',
                    width: 45,
                    height: 25,
                },
            },
        ],
        daysUntilDelivery: 5,
        transportType: {
            name: 'Standardní',
            code: 'common',
        },
        stores: null,
        personalPickup: false,
    },
    {
        uuid: '023f32a5-57ce-4e77-b002-20403f91006d',
        name: 'PPL',
        description: null,
        instruction: null,
        position: 1,
        price: {
            priceWithVat: 242,
            priceWithoutVat: 200,
            vatAmount: 42,
        },
        image: {
            url: 'http://127.0.0.1:8000/content/images/transport/default/57.jpg',
            width: 35,
            height: 20,
        },
        payments: [
            {
                description: 'Rychle, levně a spolehlivě!',
                instruction: null,
                uuid: '834cbcbd-3b6c-4c7b-8b19-313067a636a0',
                name: 'Kreditní kartou',
                price: {
                    priceWithVat: 100,
                    priceWithoutVat: 100,
                    vatAmount: 0,
                },
                image: {
                    url: 'http://127.0.0.1:8000/content/images/payment/default/53.jpg',
                    width: 45,
                    height: 25,
                },
            },
            {
                description: null,
                instruction: null,
                uuid: '256531fd-a95b-4074-b440-0e43105a15fb',
                name: 'GoPay - Platba kartou',
                price: {
                    priceWithVat: 0,
                    priceWithoutVat: 0,
                    vatAmount: 0,
                },
                image: null,
            },
        ],
        daysUntilDelivery: 4,
        transportType: {
            name: 'Standardní',
            code: 'common',
        },
        stores: null,
        personalPickup: false,
    },
    {
        uuid: '273cd96b-cd9f-4af9-8a95-f921e56fab65',
        name: 'Osobní převzetí',
        description: 'Uvítá Vás milý personál!',
        instruction: null,
        position: 2,
        price: {
            priceWithVat: 0,
            priceWithoutVat: 0,
            vatAmount: 0,
        },
        image: {
            url: 'http://127.0.0.1:8000/content/images/transport/default/58.jpg',
            width: 35,
            height: 20,
        },
        payments: [
            {
                description: 'Rychle, levně a spolehlivě!',
                instruction: null,
                uuid: '834cbcbd-3b6c-4c7b-8b19-313067a636a0',
                name: 'Kreditní kartou',
                price: {
                    priceWithVat: 100,
                    priceWithoutVat: 100,
                    vatAmount: 0,
                },
                image: {
                    url: 'http://127.0.0.1:8000/content/images/payment/default/53.jpg',
                    width: 45,
                    height: 25,
                },
            },
            {
                description: null,
                instruction: null,
                uuid: 'bde0fcd1-5ae2-4911-8274-c56e278d51cd',
                name: 'Hotově',
                price: {
                    priceWithVat: 0,
                    priceWithoutVat: 0,
                    vatAmount: 0,
                },
                image: {
                    url: 'http://127.0.0.1:8000/content/images/payment/default/54.jpg',
                    width: 45,
                    height: 25,
                },
            },
            {
                description: null,
                instruction: null,
                uuid: '256531fd-a95b-4074-b440-0e43105a15fb',
                name: 'GoPay - Platba kartou',
                price: {
                    priceWithVat: 0,
                    priceWithoutVat: 0,
                    vatAmount: 0,
                },
                image: null,
            },
        ],
        daysUntilDelivery: 0,
        transportType: {
            name: 'Standardní',
            code: 'common',
        },
        stores: {
            edges: [
                {
                    node: {
                        description: null,
                        openingHours: 'Po-Pa: 8:00-16:00',
                        contactInfo: null,
                        specialMessage: null,
                        locationLatitude: '49.8574975000000',
                        locationLongitude: '18.2738861000000',
                    },
                },
                {
                    node: {
                        description: null,
                        openingHours: 'Po-Pa: 8:00-17:00',
                        contactInfo: null,
                        specialMessage: null,
                        locationLatitude: '50.0346875000000',
                        locationLongitude: '15.7707169000000',
                    },
                },
            ],
        },
        personalPickup: true,
    },
    {
        uuid: '6a366c9a-c657-457c-bab9-b52a899816e8',
        name: 'Nadlimitní',
        description: 'Vhodné pro nadměrné zboží',
        instruction: null,
        position: 3,
        price: {
            priceWithVat: 0,
            priceWithoutVat: 0,
            vatAmount: 0,
        },
        image: null,
        payments: [
            {
                description: null,
                instruction: null,
                uuid: '3967f722-5b1e-49a2-a23a-fd2664c40fd6',
                name: 'Nadlimitní',
                price: {
                    priceWithVat: 200,
                    priceWithoutVat: 200,
                    vatAmount: 0,
                },
                image: null,
            },
        ],
        daysUntilDelivery: 0,
        transportType: {
            name: 'Standardní',
            code: 'common',
        },
        stores: null,
        personalPickup: false,
    },
];

const TransportAndPayment: FC<ServerSidePropsType> = (props) => {
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
    return initServerSideProps(context, [navigationQuery]);
};

export default TransportAndPayment;
