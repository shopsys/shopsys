import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { PhonePrefixSelect } from 'components/Forms/PhonePrefixSelect/PhonePrefixSelect';
import { TextInput } from 'components/Forms/TextInput/TextInput';
import { TIDs } from 'cypress/tids';
import { ChangeEventHandler, FocusEventHandler, ReactNode, useCallback, useEffect } from 'react';
import { UseFormReturn, useController } from 'react-hook-form';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { usePhonePrefixes } from 'utils/phonePrefix/usePhonePrefixes';

type PhoneNumberInputControlledProps = {
    formProviderMethods: UseFormReturn<any>;
    formName: string;
    /** Form field name for the dial code (e.g. "telephonePrefix"). Bound to the string "+420". */
    prefixName: string;
    /** Form field name for the country code (e.g. "telephonePrefixCountryCode"). Bound to the string "CZ". */
    prefixCountryCodeName: string;
    onPrefixChange?: (dialCode: string, countryCode: string) => void;
    isDisabled?: boolean;
    telephoneName: string;
    telephoneLabel: ReactNode;
    telephoneOnChange?: ChangeEventHandler<HTMLInputElement>;
    isTelephoneRequired?: boolean;
};

export const PhoneNumberInputControlled: FC<PhoneNumberInputControlledProps> = ({
    formProviderMethods,
    formName,
    prefixName,
    prefixCountryCodeName,
    onPrefixChange,
    isDisabled = false,
    telephoneName,
    telephoneLabel,
    telephoneOnChange,
    isTelephoneRequired = true,
}) => {
    const { t } = useTranslation();
    const { phonePrefixes, defaultPhonePrefix } = usePhonePrefixes();

    const {
        field: prefixField,
        fieldState: { error: prefixError },
    } = useController({ name: prefixName, control: formProviderMethods.control });
    const { field: prefixCountryCodeField } = useController({
        name: prefixCountryCodeName,
        control: formProviderMethods.control,
    });
    const {
        field: telephoneField,
        fieldState: { error: telephoneError },
    } = useController({ name: telephoneName, control: formProviderMethods.control });

    const hasError = Boolean(prefixError) || Boolean(telephoneError);
    const activeError = telephoneError ?? prefixError;
    const telephoneInputId = `${formName}-${telephoneName}`;
    const phoneInputErrorMessageId = `${telephoneInputId}-error`;
    const prefixTid = `${formName}-${prefixName}`;

    const onPrefixSelectChange = useCallback(
        ({ dialCode, countryCode }: { dialCode: string; countryCode: string }) => {
            prefixField.onChange(dialCode);
            prefixCountryCodeField.onChange(countryCode);
            onPrefixChange?.(dialCode, countryCode);
        },
        [prefixField, prefixCountryCodeField, onPrefixChange],
    );

    const onTelephoneBlur: FocusEventHandler<HTMLInputElement> = () => {
        telephoneField.onBlur();
        window.getSelection()?.removeAllRanges();
    };

    const onTelephoneChange: ChangeEventHandler<HTMLInputElement> = (event) => {
        telephoneField.onChange(event);
        telephoneOnChange?.(event);
    };

    useEffect(() => {
        if (!defaultPhonePrefix) {
            return;
        }

        const currentPrefixValue = prefixField.value ?? '';
        const hasSelectedPhonePrefix = phonePrefixes.some((phonePrefix) => phonePrefix.dialCode === currentPrefixValue);

        if (hasSelectedPhonePrefix) {
            return;
        }

        formProviderMethods.setValue(prefixName, defaultPhonePrefix.dialCode, {
            shouldValidate: true,
        });
        formProviderMethods.setValue(prefixCountryCodeName, defaultPhonePrefix.code, {
            shouldValidate: true,
        });
    }, [defaultPhonePrefix, formProviderMethods, phonePrefixes, prefixCountryCodeName, prefixField.value, prefixName]);

    return (
        <FormColumn>
            <FormLine width="narrow">
                <PhonePrefixSelect
                    ariaLabel={t('Dial code')}
                    availablePrefixes={phonePrefixes}
                    countryCode={prefixCountryCodeField.value ?? ''}
                    describedBy={prefixError ? phoneInputErrorMessageId : undefined}
                    dialCode={prefixField.value ?? ''}
                    hasError={Boolean(prefixError)}
                    isDisabled={isDisabled}
                    isRequired={isTelephoneRequired}
                    label={t('Dial code')}
                    tid={prefixTid}
                    onChange={onPrefixSelectChange}
                />
            </FormLine>

            <FormLine width="wide">
                <TextInput
                    aria-describedby={telephoneError ? phoneInputErrorMessageId : undefined}
                    aria-invalid={Boolean(telephoneError)}
                    aria-label={typeof telephoneLabel === 'string' ? telephoneLabel : undefined}
                    autoComplete="tel"
                    disabled={isDisabled}
                    hasError={hasError}
                    id={telephoneInputId}
                    label={telephoneLabel}
                    name={telephoneField.name}
                    required={isTelephoneRequired}
                    type="tel"
                    value={telephoneField.value ?? ''}
                    onBlur={onTelephoneBlur}
                    onChange={onTelephoneChange}
                />

                {activeError?.message !== undefined && (
                    <span
                        className="mt-1 block font-secondary text-sm text-text-error"
                        data-tid={TIDs.form_line_error}
                        id={phoneInputErrorMessageId}
                    >
                        {activeError.message}
                    </span>
                )}
            </FormLine>
        </FormColumn>
    );
};
