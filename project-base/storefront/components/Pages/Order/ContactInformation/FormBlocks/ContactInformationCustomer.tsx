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
        <fieldset className={isCompanyUser ? 'hidden' : 'rounded-t-xl bg-background-more'}>
            <div className="mx-5 vl:mx-20 mt-5 vl:mt-8 flex vl:flex-row flex-col vl:items-center gap-2 vl:gap-8 border-border-less border-b pb-5">
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
