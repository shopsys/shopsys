import { FormColumn } from 'components/Forms/Lib/FormColumn/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError/FormLineError';
import { RadiobuttonGroup } from 'components/Forms/Radiobutton/RadiobuttonGroup';
import { TextInput } from 'components/Forms/TextInput/TextInput';
import { useRegistrationFormMeta } from 'components/Pages/Registration/formMeta';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import { CustomerTypeEnum } from 'types/customer';
import { RegistrationFormType } from 'types/form';

export const User: FC = () => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<RegistrationFormType>();
    const formMeta = useRegistrationFormMeta(formProviderMethods);

    return (
        <>
            <FormLine bottomGap>
                <Controller
                    name={formMeta.fields.email.name}
                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                        <>
                            <TextInput
                                id={formMeta.formName + '-' + formMeta.fields.email.name}
                                name={formMeta.fields.email.name}
                                label={formMeta.fields.email.label}
                                required
                                type="text"
                                isTouched={isTouched}
                                hasError={invalid}
                                fieldRef={field}
                            />
                            <FormLineError
                                error={error}
                                inputType="text-input"
                                testIdentifier={formMeta.formName + '-' + formMeta.fields.email.name + '-error'}
                            />
                        </>
                    )}
                />
            </FormLine>

            <FormLine bottomGap>
                <Controller
                    name={formMeta.fields.firstName.name}
                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                        <>
                            <TextInput
                                id={formMeta.formName + '-' + formMeta.fields.firstName.name}
                                name={formMeta.fields.firstName.name}
                                label={formMeta.fields.firstName.label}
                                required
                                type="text"
                                isTouched={isTouched}
                                hasError={invalid}
                                fieldRef={field}
                            />
                            <FormLineError
                                error={error}
                                inputType="text-input"
                                testIdentifier={formMeta.formName + '-' + formMeta.fields.firstName.name + '-error'}
                            />
                        </>
                    )}
                />
            </FormLine>
            <FormLine bottomGap>
                <Controller
                    name={formMeta.fields.lastName.name}
                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                        <>
                            <TextInput
                                id={formMeta.formName + '-' + formMeta.fields.lastName.name}
                                name={formMeta.fields.lastName.name}
                                label={formMeta.fields.lastName.label}
                                required
                                type="text"
                                isTouched={isTouched}
                                hasError={invalid}
                                fieldRef={field}
                            />
                            <FormLineError
                                error={error}
                                inputType="text-input"
                                testIdentifier={formMeta.formName + '-' + formMeta.fields.lastName.name + '-error'}
                            />
                        </>
                    )}
                />
            </FormLine>
            <FormLine bottomGap>
                <Controller
                    name={formMeta.fields.telephone.name}
                    render={({ fieldState: { isTouched, invalid, error }, field }) => (
                        <>
                            <TextInput
                                id={formMeta.formName + '-' + formMeta.fields.telephone.name}
                                name={formMeta.fields.telephone.name}
                                label={formMeta.fields.telephone.label}
                                required
                                type="text"
                                isTouched={isTouched}
                                hasError={invalid}
                                fieldRef={field}
                            />
                            <FormLineError
                                error={error}
                                inputType="text-input"
                                testIdentifier={formMeta.formName + '-' + formMeta.fields.telephone.name + '-error'}
                            />
                        </>
                    )}
                />
            </FormLine>
            <FormColumn lg="65%">
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
                        <FormLine key={key} bottomGap width="100%" lg="50%">
                            {radiobutton}
                        </FormLine>
                    )}
                />
            </FormColumn>
        </>
    );
};
