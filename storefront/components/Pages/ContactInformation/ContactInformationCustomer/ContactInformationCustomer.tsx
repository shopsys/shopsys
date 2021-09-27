import { Controller } from 'react-hook-form';
import { FC } from 'react';
import FormColumn from 'components/Forms/Lib/FormColumn';
import FormLine from 'components/Forms/Lib/FormLine';
import Heading from 'components/Basic/Heading';
import Radiobutton from 'components/Forms/Radiobutton';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type ContactInformationCustomer = {
    isCompanyCustomerChecked: boolean;
    isCommonCustomerChecked: boolean;
    onChangeCustomerValue: () => void;
};

const ContactInformationCustomer: FC<ContactInformationCustomer> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <>
            <Heading type="h3">{t('You will shop with us like')}</Heading>
            <Controller
                name="customer"
                render={({ field }) => (
                    <div onChange={props.onChangeCustomerValue}>
                        <FormColumn Lg="65%">
                            <FormLine bottomGap={true} Width="100%" Lg="50%">
                                <Radiobutton
                                    name={field.name}
                                    id="contactInformation_form-commonCustomer"
                                    value="commonCustomer"
                                    label={t('Private person')}
                                    fieldRef={field}
                                    checked={props.isCommonCustomerChecked}
                                />
                            </FormLine>
                            <FormLine bottomGap={true} Width="100%" Lg="50%">
                                <Radiobutton
                                    name={field.name}
                                    id="contactInformation_form-companyCustomer"
                                    value="companyCustomer"
                                    label={t('Company')}
                                    fieldRef={field}
                                    checked={props.isCompanyCustomerChecked}
                                />
                            </FormLine>
                        </FormColumn>
                    </div>
                )}
            />
        </>
    );
};
/* @component */
export default ContactInformationCustomer;
