import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { RadiobuttonGroup } from 'components/Forms/Radiobutton/RadiobuttonGroup';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useRegistrationFormMeta } from 'components/Pages/Registration/formMeta';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useFormContext } from 'react-hook-form';
import { CustomerTypeEnum } from 'types/customer';
import { RegistrationFormType } from 'types/form';

export const User: FC = () => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<RegistrationFormType>();
    const formMeta = useRegistrationFormMeta(formProviderMethods);

    return (
        <>
            <TextInputControlled
                control={formProviderMethods.control}
                name={formMeta.fields.email.name}
                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                formName={formMeta.formName}
                textInputProps={{
                    label: formMeta.fields.email.label,
                    required: true,
                    type: 'email',
                    autoComplete: 'email',
                }}
            />
            <TextInputControlled
                control={formProviderMethods.control}
                name={formMeta.fields.firstName.name}
                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                formName={formMeta.formName}
                textInputProps={{
                    label: formMeta.fields.firstName.label,
                    required: true,
                    type: 'text',
                    autoComplete: 'given-name',
                }}
            />
            <TextInputControlled
                control={formProviderMethods.control}
                name={formMeta.fields.lastName.name}
                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                formName={formMeta.formName}
                textInputProps={{
                    label: formMeta.fields.lastName.label,
                    required: true,
                    type: 'text',
                    autoComplete: 'family-name',
                }}
            />
            <TextInputControlled
                control={formProviderMethods.control}
                name={formMeta.fields.telephone.name}
                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                formName={formMeta.formName}
                textInputProps={{
                    label: formMeta.fields.telephone.label,
                    required: true,
                    type: 'tel',
                    autoComplete: 'tel',
                }}
            />
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
