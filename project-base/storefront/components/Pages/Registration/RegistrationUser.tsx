import { FormBlockWrapper } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { RadiobuttonGroup } from 'components/Forms/Radiobutton/RadiobuttonGroup';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useRegistrationFormMeta } from 'components/Pages/Registration/registrationFormMeta';
import useTranslation from 'next-translate/useTranslation';
import { useFormContext } from 'react-hook-form';
import { CustomerTypeEnum } from 'types/customer';
import { RegistrationFormType } from 'types/form';

export const RegistrationUser: FC = () => {
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<RegistrationFormType>();
    const formMeta = useRegistrationFormMeta(formProviderMethods);

    return (
        <FormBlockWrapper>
            <fieldset>
                <legend className="h4 mb-4">{t('Personal data')}</legend>

                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.email.name}
                    render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                    textInputProps={{
                        label: formMeta.fields.email.label,
                        required: true,
                        type: 'email',
                        autoComplete: 'email',
                        'aria-labelledby': 'registration-form-description',
                    }}
                />
                <FormColumn>
                    <TextInputControlled
                        control={formProviderMethods.control}
                        formName={formMeta.formName}
                        name={formMeta.fields.firstName.name}
                        render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                        textInputProps={{
                            label: formMeta.fields.firstName.label,
                            required: true,
                            type: 'text',
                            autoComplete: 'given-name',
                        }}
                    />

                    <TextInputControlled
                        control={formProviderMethods.control}
                        formName={formMeta.formName}
                        name={formMeta.fields.lastName.name}
                        render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                        textInputProps={{
                            label: formMeta.fields.lastName.label,
                            required: true,
                            type: 'text',
                            autoComplete: 'family-name',
                        }}
                    />
                </FormColumn>

                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.telephone.name}
                    render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                    textInputProps={{
                        label: formMeta.fields.telephone.label,
                        required: true,
                        type: 'tel',
                        autoComplete: 'tel',
                    }}
                />
            </fieldset>

            <fieldset>
                <legend className="sr-only">{t('Customer type')}</legend>

                <FormColumn className="vl:gap-0 gap-2">
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
                    />
                </FormColumn>
            </fieldset>
        </FormBlockWrapper>
    );
};
