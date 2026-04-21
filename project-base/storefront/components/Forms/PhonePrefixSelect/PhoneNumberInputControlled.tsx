import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { PhonePrefixSelect } from 'components/Forms/PhonePrefixSelect/PhonePrefixSelect';
import { TextInput } from 'components/Forms/TextInput/TextInput';
import { TIDs } from 'cypress/tids';
import { ChangeEvent, ChangeEventHandler, FocusEventHandler, ReactNode, useCallback, useEffect, useRef } from 'react';
import { UseFormReturn, useController } from 'react-hook-form';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { parsePhoneWithPrefix } from 'utils/phonePrefix/parsePhoneWithPrefix';
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
    const onPrefixFieldChange = prefixField.onChange;
    const onPrefixCountryCodeFieldChange = prefixCountryCodeField.onChange;

    const onPrefixSelectChange = useCallback(
        ({ dialCode, countryCode }: { dialCode: string; countryCode: string }) => {
            onPrefixFieldChange(dialCode);
            onPrefixCountryCodeFieldChange(countryCode);
            onPrefixChange?.(dialCode, countryCode);
        },
        [onPrefixCountryCodeFieldChange, onPrefixFieldChange, onPrefixChange],
    );

    const previousValueRef = useRef(telephoneField.value ?? '');

    // Keep the ref in sync when the form value changes externally (e.g. form reset, server data load).
    useEffect(() => {
        previousValueRef.current = telephoneField.value ?? '';
    }, [telephoneField.value]);

    const applyParsedPrefix = useCallback(
        (result: ReturnType<typeof parsePhoneWithPrefix>) => {
            if (!result.matched) {
                return false;
            }

            formProviderMethods.setValue(prefixName, result.dialCode, { shouldValidate: true });
            formProviderMethods.setValue(prefixCountryCodeName, result.countryCode, { shouldValidate: true });
            formProviderMethods.setValue(telephoneName, result.localNumber, { shouldValidate: true });
            previousValueRef.current = result.localNumber;

            onPrefixChange?.(result.dialCode, result.countryCode);
            // Synthetic event — consumers must only access currentTarget.value
            telephoneOnChange?.({ currentTarget: { value: result.localNumber } } as ChangeEvent<HTMLInputElement>);

            return true;
        },
        [formProviderMethods, onPrefixChange, prefixCountryCodeName, prefixName, telephoneName, telephoneOnChange],
    );

    const onTelephoneBlur: FocusEventHandler<HTMLInputElement> = (event) => {
        // Safety net: some password managers set the DOM value without firing onChange,
        // so react-hook-form state may be stale. Read the actual DOM value and parse
        // only if it differs from what RHF knows (= evidence of external DOM manipulation).
        const domValue = event.target.value;
        const rhfValue = telephoneField.value ?? '';

        if (domValue !== rhfValue) {
            const result = parsePhoneWithPrefix(domValue, phonePrefixes, prefixCountryCodeField.value);

            if (result.matched) {
                applyParsedPrefix(result);
            } else {
                formProviderMethods.setValue(telephoneName, domValue, { shouldValidate: true });
                previousValueRef.current = domValue;
                telephoneOnChange?.({ currentTarget: { value: domValue } } as ChangeEvent<HTMLInputElement>);
            }
        }

        telephoneField.onBlur();
        window.getSelection()?.removeAllRanges();
    };

    const onTelephoneChange: ChangeEventHandler<HTMLInputElement> = (event) => {
        const newValue = event.target.value;
        const oldValue = previousValueRef.current;
        previousValueRef.current = newValue;

        // Detect paste / autofill: value changed by more than one character at once.
        const isPasteOrAutofill = Math.abs(newValue.length - oldValue.length) > 1;

        // Detect explicit international prefix typed character by character.
        const hasExplicitPrefix = newValue.startsWith('+') || newValue.startsWith('00');

        if (isPasteOrAutofill || hasExplicitPrefix) {
            const result = parsePhoneWithPrefix(newValue, phonePrefixes, prefixCountryCodeField.value);

            if (applyParsedPrefix(result)) {
                return;
            }
        }

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
