import { Heading } from 'components/Basic/Heading/Heading';
import { FormColumn } from 'components/Forms/Lib/FormColumn/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { Radiobutton } from 'components/Forms/Radiobutton/Radiobutton';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/formMeta';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import { CustomerTypeEnum } from 'types/customer';
import { ContactInformationFormType } from 'types/form';

export const ContactInformationCustomer: FC = () => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<ContactInformationFormType>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);

    return (
        <>
            <Heading type="h3">{formMeta.fields.customer.label}</Heading>
            <Controller
                name={formMeta.fields.customer.name}
                render={({ field }) => (
                    <>
                        <FormColumn lg="65%">
                            <FormLine bottomGap={true} width="100%" lg="50%">
                                <Radiobutton
                                    name={formMeta.fields.customer.name}
                                    id={formMeta.formName + '-' + CustomerTypeEnum.CommonCustomer}
                                    value={CustomerTypeEnum.CommonCustomer}
                                    label={t('Private person')}
                                    fieldRef={field}
                                />
                            </FormLine>
                            <FormLine bottomGap={true} width="100%" lg="50%">
                                <Radiobutton
                                    name={formMeta.fields.customer.name}
                                    id={formMeta.formName + '-' + CustomerTypeEnum.CompanyCustomer}
                                    value={CustomerTypeEnum.CompanyCustomer}
                                    label={t('Company')}
                                    fieldRef={field}
                                />
                            </FormLine>
                        </FormColumn>
                    </>
                )}
            />
        </>
    );
};
