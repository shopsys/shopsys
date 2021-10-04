import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import CommonLayout from 'components/Layout/CommonLayout';
import ContactInformationForm from 'components/Pages/ContactInformation';
import { FC } from 'react';
import Form from 'components/Forms/Form';
import { getContactInformationFormResolver } from 'components/Pages/ContactInformation/ContactInformationFormResolver';
import { GetServerSideProps } from 'next';
import { navigationQuery } from 'connectors/navigation/Navigation';
import OrderSteps from 'components/Blocks/OrderSteps';
import OrderSummary from 'components/Blocks/OrderSummary';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { TFunction } from 'next-i18next';
import { useInitDomainConfig } from 'hooks/helpers/UseInitDomainConfig';
import { useShopsysSelector } from 'redux/store';
import Webline from 'components/Layout/Webline';

export const getCountrySelectOptions = (t: TFunction): { value: string; label: string }[] => [
    { value: 'slovakia', label: t('Slovakia') },
    { value: 'czech', label: t('Czech Republic') },
];

const ContactInformation: FC<ServerSidePropsType> = (props) => {
    useInitDomainConfig(props.domainConfig);
    const cart = useShopsysSelector((state) => state.user.cart);
    const t = useTypedTranslationFunction();

    return (
        <StaticUrlGuard domainUrl={props.domainConfig.url}>
            <CommonLayout>
                <OrderSteps activeStep={3} domainUrl={props.domainConfig.url} />
                {cart === undefined ? null : <OrderSummary cart={cart} />}
                <Form
                    resolver={getContactInformationFormResolver()}
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
                    <Webline>
                        <ContactInformationForm />
                    </Webline>
                </Form>
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps: GetServerSideProps = async (context) => {
    return initServerSideProps(context, [navigationQuery]);
};

export default ContactInformation;
