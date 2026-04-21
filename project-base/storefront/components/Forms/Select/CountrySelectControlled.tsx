import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { Select } from 'components/Forms/Select/Select';
import { ReactNode, useCallback, useEffect } from 'react';
import { UseFormReturn, useController } from 'react-hook-form';
import { SelectOptionType } from 'types/selectOptions';
import { useCountriesAsSelectOptions } from 'utils/countries/useCountriesAsSelectOptions';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type CountrySelectControlledProps = {
    formProviderMethods: UseFormReturn<any>;
    formName: string;
    name: string;
    label: ReactNode;
    isDisabled?: boolean;
    isRequired?: boolean;
    onCountryChange?: (country: SelectOptionType) => void;
};

export const CountrySelectControlled: FC<CountrySelectControlledProps> = ({
    formProviderMethods,
    formName,
    name,
    label,
    isDisabled = false,
    isRequired = true,
    onCountryChange,
}) => {
    const { t } = useTranslation();
    const countriesAsSelectOptions = useCountriesAsSelectOptions();
    const {
        field,
        fieldState: { error },
    } = useController({ name, control: formProviderMethods.control });

    const activeOption = field.value && countriesAsSelectOptions.find((option) => option.value === field.value.value);

    const onSelectOption = useCallback(
        (country: SelectOptionType) => {
            field.onChange(country);
            onCountryChange?.(country);
        },
        [field, onCountryChange],
    );

    useEffect(() => {
        if (countriesAsSelectOptions.length === 0) {
            return;
        }

        const selectedCountry =
            countriesAsSelectOptions.find((country) => country.value === field.value?.value) ??
            countriesAsSelectOptions[0];
        const hasSelectedCountry =
            field.value?.value === selectedCountry.value && field.value?.label === selectedCountry.label;

        if (hasSelectedCountry) {
            return;
        }

        formProviderMethods.setValue(name, selectedCountry, {
            shouldValidate: true,
        });
    }, [countriesAsSelectOptions, field.value?.label, field.value?.value, formProviderMethods, name]);

    return (
        <FormColumn>
            <FormLine width="wide">
                <Select
                    activeOption={activeOption}
                    ariaLabel={t('Select country', { ns: 'accessibility' })}
                    isDisabled={isDisabled}
                    isRequired={isRequired}
                    label={label}
                    options={countriesAsSelectOptions}
                    tid={`${formName}-${name}`}
                    onSelectOption={onSelectOption}
                />
                <FormLineError error={error} inputType="select" />
            </FormLine>
        </FormColumn>
    );
};
