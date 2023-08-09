import { Heading } from 'components/Basic/Heading/Heading';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { RadiobuttonGroup } from 'components/Forms/Radiobutton/RadiobuttonGroup';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useFormContext } from 'react-hook-form';
import { ContactInformation } from 'store/zustand/slices/createContactInformationSlice';
import { CustomerTypeEnum } from 'types/customer';

export const ContactInformationCustomer: FC = () => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<ContactInformation>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);

    return (
        <>
            <Heading type="h3">{formMeta.fields.customer.label}</Heading>
            <FormColumn className="lg:w-[calc(65%+0.75rem)]">
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
                        <FormLine key={key} bottomGap className="w-full flex-none lg:w-1/2">
                            {radiobutton}
                        </FormLine>
                    )}
                />
            </FormColumn>
        </>
    );
};
