import { Controller, useFormContext, useWatch } from 'react-hook-form';
import {
    CustomerTypeEnum,
    RegistrationFormType,
    useRegistrationFormMeta,
} from 'components/Pages/Registration/formMeta';
import { FC } from 'react';
import FormColumn from 'components/Forms/Lib/FormColumn';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import Radiobutton from 'components/Forms/Radiobutton';
import TextInput from 'components/Forms/TextInput';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const User: FC = () => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<RegistrationFormType>();
    const formMeta = useRegistrationFormMeta(formProviderMethods);
    const customerValue = useWatch({ name: formMeta.fields.customer.name, control: formProviderMethods.control });

    return (
        <>
            <FormLine bottomGap={true}>
                <Controller
                    name={formMeta.fields.email.name}
                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                        <>
                            <TextInput
                                id={formMeta.formName + '-' + formMeta.fields.email.name}
                                name={formMeta.fields.email.name}
                                label={formMeta.fields.email.label}
                                required={true}
                                type="text"
                                isTouched={isTouched}
                                hasError={invalid}
                                fieldRef={field}
                            />
                            <FormLineError error={error} inputType="text-input" />
                        </>
                    )}
                />
            </FormLine>

            <FormLine bottomGap={true}>
                <Controller
                    name={formMeta.fields.firstName.name}
                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                        <>
                            <TextInput
                                id={formMeta.formName + '-' + formMeta.fields.firstName.name}
                                name={formMeta.fields.firstName.name}
                                label={formMeta.fields.firstName.label}
                                required={true}
                                type="text"
                                isTouched={isTouched}
                                hasError={invalid}
                                fieldRef={field}
                            />
                            <FormLineError error={error} inputType="text-input" />
                        </>
                    )}
                />
            </FormLine>
            <FormLine bottomGap={true}>
                <Controller
                    name={formMeta.fields.lastName.name}
                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                        <>
                            <TextInput
                                id={formMeta.formName + '-' + formMeta.fields.lastName.name}
                                name={formMeta.fields.lastName.name}
                                label={formMeta.fields.lastName.label}
                                required={true}
                                type="text"
                                isTouched={isTouched}
                                hasError={invalid}
                                fieldRef={field}
                            />
                            <FormLineError error={error} inputType="text-input" />
                        </>
                    )}
                />
            </FormLine>
            <FormLine bottomGap={true}>
                <Controller
                    name={formMeta.fields.telephone.name}
                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                        <>
                            <TextInput
                                id={formMeta.formName + '-' + formMeta.fields.telephone.name}
                                name={formMeta.fields.telephone.name}
                                label={formMeta.fields.telephone.label}
                                required={true}
                                type="text"
                                isTouched={isTouched}
                                hasError={invalid}
                                fieldRef={field}
                            />
                            <FormLineError error={error} inputType="text-input" />
                        </>
                    )}
                />
            </FormLine>

            <Controller
                name={formMeta.fields.customer.name}
                render={({ field }) => (
                    <>
                        <FormColumn lg="65%">
                            <FormLine bottomGap={true} width="100%" lg="50%">
                                <Radiobutton
                                    name={formMeta.fields.customer.name}
                                    id={formMeta.formName + '-' + CustomerTypeEnum.CommonCustomer}
                                    value={CustomerTypeEnum.CommonCustomer}
                                    label={t('Private person')}
                                    fieldRef={field}
                                    checked={customerValue === CustomerTypeEnum.CommonCustomer}
                                />
                            </FormLine>
                            <FormLine bottomGap={true} width="100%" lg="50%">
                                <Radiobutton
                                    name={formMeta.fields.customer.name}
                                    id={formMeta.formName + '-' + CustomerTypeEnum.CompanyCustomer}
                                    value={CustomerTypeEnum.CompanyCustomer}
                                    label={t('Company')}
                                    fieldRef={field}
                                    checked={customerValue === CustomerTypeEnum.CompanyCustomer}
                                />
                            </FormLine>
                        </FormColumn>
                    </>
                )}
            />
        </>
    );
};

/* @component */
export default User;
