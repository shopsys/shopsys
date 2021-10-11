import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import ContactInformationForm from 'components/Pages/ContactInformation';
import { FC } from 'react';
import Form from 'components/Forms/Form';
import { getContactInformationFormResolver } from 'components/Pages/ContactInformation/ContactInformationFormResolver';
import { navigationQuery } from 'connectors/navigation/Navigation';
import { nextReduxWrapper } from 'redux/main';
import OrderLayout from 'components/Layout/OrderLayout';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { TFunction } from 'next-i18next';
import { useInitDomainConfig } from 'hooks/helpers/UseInitDomainConfig';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export const getCountrySelectOptions = (t: TFunction): { value: string; label: string }[] => [
    { value: 'slovakia', label: t('Slovakia') },
    { value: 'czech', label: t('Czech Republic') },
];

const ContactInformation: FC<ServerSidePropsType> = (props) => {
    useInitDomainConfig(props.domainConfig);
    const t = useTypedTranslationFunction();

    return (
        <StaticUrlGuard domainUrl={props.domainConfig.url}>
            <Form
                resolver={getContactInformationFormResolver(t)}
                defaultValues={{
                    email: '',
                    register: false,
                    passwordFirst: '',
                    passwordSecond: '',
                    customer: 'commonCustomer',
                    telephone: '',
                    firstName: '',
                    lastName: '',
                    street: '',
                    city: '',
                    postcode: '',
                    country: getCountrySelectOptions(t)[0].label,
                    companyName: '',
                    companyNumber: '',
                    companyTaxNumber: '',
                    deliveryAddress: false,
                    deliveryFirstName: '',
                    deliveryLastName: '',
                    deliveryCompanyName: '',
                    deliveryTelephone: '',
                    deliveryStreet: '',
                    deliveryCity: '',
                    deliveryPostcode: '',
                    deliveryCountry: getCountrySelectOptions(t)[0].label,
                    newsletterSubscription: false,
                }}
            >
                <OrderLayout activeStep={3} buttonNextText={t('Submit order')}>
                    <ContactInformationForm />
                </OrderLayout>
            </Form>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    return initServerSideProps(context, store, [navigationQuery]);
});

export default ContactInformation;
