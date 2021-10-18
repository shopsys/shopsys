import { Controller } from 'react-hook-form';
import { FC } from 'react';
import FormColumn from 'components/Forms/Lib/FormColumn';
import FormLine from 'components/Forms/Lib/FormLine';
import Heading from 'components/Basic/Heading';
import Radiobutton from 'components/Forms/Radiobutton';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type ContactInformationCustomer = { currentValue: 'commonCustomer' | 'companyCustomer' };

const ContactInformationCustomer: FC<ContactInformationCustomer> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <>
            <Heading type="h3">{t('You will shop with us like')}</Heading>
            <Controller
                name="customer"
                render={({ field }) => (
                    <>
                        <FormColumn lg="65%">
                            <FormLine bottomGap={true} width="100%" lg="50%">
                                <Radiobutton
                                    name={field.name}
                                    id="contactInformation_form-commonCustomer"
                                    value="commonCustomer"
                                    label={t('Private person')}
                                    fieldRef={field}
                                    checked={props.currentValue === 'commonCustomer'}
                                />
                            </FormLine>
                            <FormLine bottomGap={true} width="100%" lg="50%">
                                <Radiobutton
                                    name={field.name}
                                    id="contactInformation_form-companyCustomer"
                                    value="companyCustomer"
                                    label={t('Company')}
                                    fieldRef={field}
                                    checked={props.currentValue === 'companyCustomer'}
                                />
                            </FormLine>
                        </FormColumn>
                    </>
                )}
            />
        </>
    );
};
/* @component */
export default ContactInformationCustomer;
