import { FormHeading } from 'components/Forms/Form/Form';
import { RadiobuttonGroup } from 'components/Forms/Radiobutton/RadiobuttonGroup';
import { useRegistrationFormMeta } from 'components/Pages/Registration/registrationFormMeta';
import { useFormContext } from 'react-hook-form';
import { CustomerTypeEnum } from 'types/customer';
import { RegistrationFormType } from 'types/form';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const RegistrationCustomer: FC = () => {
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<RegistrationFormType>();
    const formMeta = useRegistrationFormMeta();

    return (
        <fieldset className="bg-background-more rounded-t-xl">
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
                        },
                        {
                            label: t('Company'),
                            value: CustomerTypeEnum.CompanyCustomer,
                        },
                    ]}
                />
            </div>
        </fieldset>
    );
};
