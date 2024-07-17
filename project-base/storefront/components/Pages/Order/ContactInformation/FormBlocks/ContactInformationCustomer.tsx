import { FormBlockWrapper, FormHeading } from 'components/Forms/Form/Form';
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
        <FormBlockWrapper>
            <FormHeading>{formMeta.fields.customer.label}</FormHeading>
            <FormColumn className="gap-2 vl:gap-0">
                <RadiobuttonGroup
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.customer.name}
                    render={(radiobutton, key) => <FormLine key={key}>{radiobutton}</FormLine>}
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
        </FormBlockWrapper>
    );
};
