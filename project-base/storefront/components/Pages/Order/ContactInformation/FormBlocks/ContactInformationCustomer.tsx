import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { RadiobuttonGroup } from 'components/Forms/Radiobutton/RadiobuttonGroup';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import useTranslation from 'next-translate/useTranslation';
import { useFormContext } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { CustomerTypeEnum } from 'types/customer';

export const ContactInformationCustomer: FC = () => {
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<ContactInformation>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);

    return (
        <>
            <div className="h4 mb-3">{formMeta.fields.customer.label}</div>
            <FormColumn className="lg:w-[calc(65%+0.75rem)]">
                <RadiobuttonGroup
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.customer.name}
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
                    onChange={(event) =>
                        updateContactInformation({
                            customer:
                                event.currentTarget.value === CustomerTypeEnum.CommonCustomer
                                    ? CustomerTypeEnum.CommonCustomer
                                    : CustomerTypeEnum.CompanyCustomer,
                        })
                    }
                />
            </FormColumn>
        </>
    );
};
