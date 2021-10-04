import { createContext, FC } from 'react';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import CommonLayout from 'components/Layout/CommonLayout';
import ContactInformationForm from 'components/Pages/ContactInformation';
import Form from 'components/Forms/Form';
import { getContactInformationFormResolver } from 'components/Pages/ContactInformation/ContactInformationFormResolver';
import { GetServerSideProps } from 'next';
import { navigationQuery } from 'connectors/navigation/Navigation';
import OrderSteps from 'components/Blocks/OrderSteps';
import OrderSummary from 'components/Blocks/OrderSummary';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useInitDomainConfig } from 'hooks/helpers/UseInitDomainConfig';
import { useShopsysSelector } from 'redux/store';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

export const ContactInformationContext = createContext<{ value: string; label: string }[]>(
    {} as { value: string; label: string }[],
);

const countrySelectOptions = [
    { value: 'slovakia', label: 'Slovensko' },
    { value: 'czech', label: 'Česká republika' },
];

const ContactInformation: FC<ServerSidePropsType> = (props) => {
    useInitDomainConfig(props.domainConfig);
    const t = useTypedTranslationFunction();
    const cart = useShopsysSelector((state) => state.user.cart);

    return (
        <StaticUrlGuard domainUrl={props.domainConfig.url}>
            <CommonLayout>
                <OrderSteps activeStep={3} domainUrl={props.domainConfig.url} />
                {cart === undefined ? null : <OrderSummary cart={cart} />}
                <ContactInformationContext.Provider value={countrySelectOptions}>
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
                            country: countrySelectOptions[0].label,
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
                            deliveryCountry: countrySelectOptions[0].label,
                            newsletterSubscription: false,
                        }}
                    >
                        <Webline>
                            <ContactInformationForm />
                        </Webline>
                    </Form>
                </ContactInformationContext.Provider>
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps: GetServerSideProps = async (context) => {
    return initServerSideProps(context, [navigationQuery]);
};

export default ContactInformation;
