'use client';

import { useRegistrationFormMeta } from 'app/_components/Registration/registrationFormMeta';
import { FormHeading, FormBlockWrapper } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { Select } from 'components/Forms/Select/Select';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import useTranslation from 'next-translate/useTranslation';
import { Controller, useFormContext } from 'react-hook-form';
import { RegistrationFormType } from 'types/form';
import { SelectOptionType } from 'types/selectOptions';

type RegistrationAddressProps = {
    countries: SelectOptionType[];
};

export const RegistrationAddress: FC<RegistrationAddressProps> = ({ countries }) => {
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<RegistrationFormType>();
    const formMeta = useRegistrationFormMeta(formProviderMethods);

    return (
        <FormBlockWrapper>
            <FormHeading>{t('Billing address')}</FormHeading>
            <TextInputControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.street.name}
                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                textInputProps={{
                    label: formMeta.fields.street.label,
                    required: true,
                    type: 'text',
                    autoComplete: 'street-address',
                }}
            />
            <FormColumn>
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.city.name}
                    render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                    textInputProps={{
                        label: formMeta.fields.city.label,
                        required: true,
                        type: 'text',
                        autoComplete: 'address-level2',
                    }}
                />
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.postcode.name}
                    render={(textInput) => (
                        <FormLine bottomGap isSmallInput>
                            {textInput}
                        </FormLine>
                    )}
                    textInputProps={{
                        label: formMeta.fields.postcode.label,
                        required: true,
                        type: 'text',
                        autoComplete: 'postal-code',
                    }}
                />
            </FormColumn>
            {countries.length > 0 && (
                <FormLine>
                    <Controller
                        name={formMeta.fields.country.name}
                        render={({ fieldState: { error }, field }) => (
                            <>
                                <Select
                                    isRequired
                                    activeOption={countries.find((option) => option.value === field.value.value)}
                                    label={formMeta.fields.country.label}
                                    options={countries}
                                    tid={formMeta.formName + '-' + formMeta.fields.country.name}
                                    onSelectOption={field.onChange}
                                />
                                <FormLineError error={error} inputType="select" />
                            </>
                        )}
                    />
                </FormLine>
            )}
        </FormBlockWrapper>
    );
};
