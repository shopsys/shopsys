import { Heading } from 'components/Basic/Heading/Heading';
import { FormColumn } from 'components/Forms/Lib/FormColumn/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { RadiobuttonGroup } from 'components/Forms/Radiobutton/RadiobuttonGroup';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/formMeta';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';
import { useFormContext } from 'react-hook-form';
import { CustomerTypeEnum } from 'types/customer';
import { ContactInformationFormType } from 'types/form';

export const ContactInformationCustomer: FC = () => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<ContactInformationFormType>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);

    return (
        <>
            <Heading type="h3">{formMeta.fields.customer.label}</Heading>
            <FormColumn lg="65%">
                <RadiobuttonGroup
                    name={formMeta.fields.customer.name}
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    radiobuttons={[
                        {
                            label: t('Private person'),
                            value: CustomerTypeEnum.CommonCustomer,
                        },
                        {
                            label: t('Company'),
                            value: CustomerTypeEnum.CompanyCustomer,
                        },
                    ]}
                    render={(radiobutton, key) => (
                        <FormLine key={key} bottomGap width="100%" lg="50%">
                            {radiobutton}
                        </FormLine>
                    )}
                />
            </FormColumn>
        </>
    );
};
