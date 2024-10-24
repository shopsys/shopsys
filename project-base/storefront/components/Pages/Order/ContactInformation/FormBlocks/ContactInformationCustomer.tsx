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
import { useUserPermissions } from 'utils/auth/useUserPermissions';

export const ContactInformationCustomer: FC = () => {
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<ContactInformation>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const { isCompanyUser } = useUserPermissions();

    return (
        <FormBlockWrapper className={isCompanyUser ? 'hidden' : ''}>
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
                            disabled: formMeta.fields.customer.disabled,
                        },
                        {
                            label: t('Company'),
                            value: CustomerTypeEnum.CompanyCustomer,
                            disabled: formMeta.fields.customer.disabled,
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
