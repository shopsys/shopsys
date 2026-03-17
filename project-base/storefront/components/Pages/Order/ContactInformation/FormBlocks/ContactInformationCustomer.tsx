import { FormHeading } from 'components/Forms/Form/Form';
import { RadiobuttonGroup } from 'components/Forms/Radiobutton/RadiobuttonGroup';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useFormContext } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { CustomerTypeEnum } from 'types/customer';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const ContactInformationCustomer: FC = () => {
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<ContactInformation>();
    const formMeta = useContactInformationFormMeta();
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const { isCompanyUser } = useAuthorization();

    return (
        <fieldset className={isCompanyUser ? 'hidden' : 'bg-background-more rounded-t-xl'}>
            <div className="border-border-less vl:flex-row vl:items-center vl:gap-8 vl:mx-20 vl:mt-8 mx-5 mt-5 flex flex-col gap-2 border-b pb-5">
                <FormHeading>{formMeta.fields.customer.label}</FormHeading>

                <RadiobuttonGroup
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.customer.name}
                    render={(radiobutton, key) => <span key={key}>{radiobutton}</span>}
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
            </div>
        </fieldset>
    );
};
