import { Select } from 'components/Forms/Select/Select';
import { ReactNode, useCallback, useEffect, useMemo, useState } from 'react';
import { PhonePrefixesType } from 'utils/phonePrefix/usePhonePrefixes';
import { twMergeCustom } from 'utils/twMerge';
import { TextInput } from '../TextInput/TextInput';

const normalizeDialCode = (dialCode: string) => dialCode.replace(/\s+/g, '');
const getPhonePrefixDisplayLabel = ({ dialCode, flagEmoji }: { dialCode: string; flagEmoji: string }) =>
    `${flagEmoji} ${dialCode}`;
const getPhonePrefixFilterValue = ({
    code,
    countryName,
    dialCode,
    flagEmoji,
}: {
    code: string;
    countryName: string;
    dialCode: string;
    flagEmoji: string;
}) => `${countryName} ${code} ${flagEmoji} ${dialCode}`;

type PrefixChange = {
    countryCode: string;
    dialCode: string;
};

type PhonePrefixSelectProps = {
    ariaLabel: string;
    label: string | ReactNode;
    tid: string;
    describedBy?: string;
    /** ISO 3166-1 alpha-2 country code used for pre-selection (e.g. "CZ"). Preferred over dialCode for disambiguation. */
    countryCode: string;
    /** Dial code currently stored in the form (e.g. "+420"). Used to keep legacy/incomplete form state in sync. */
    dialCode: string;
    onChange: (change: PrefixChange) => void;
    availablePrefixes: PhonePrefixesType;
    hasError?: boolean;
    isRequired?: boolean;
    isDisabled?: boolean;
    readonly?: boolean;
    className?: string;
    selectClassName?: string;
};

export const PhonePrefixSelect: FC<PhonePrefixSelectProps> = ({
    ariaLabel,
    label,
    tid,
    describedBy,
    countryCode,
    dialCode,
    onChange,
    availablePrefixes,
    hasError = false,
    isRequired = true,
    isDisabled = false,
    readonly = false,
    className,
    selectClassName,
}) => {
    const [searchQuery, setSearchQuery] = useState('');
    const [isDropdownOpen, setIsDropdownOpen] = useState(false);
    const normalizedDialCode = normalizeDialCode(dialCode);

    const normalizedPrefixes = useMemo(
        () =>
            availablePrefixes.map((phonePrefix) => ({
                ...phonePrefix,
                dialCode: normalizeDialCode(phonePrefix.dialCode),
            })),
        [availablePrefixes],
    );

    // Options keyed by country code so each country is selectable even when sharing a dial code.
    const selectOptions = useMemo(
        () =>
            normalizedPrefixes.map((phonePrefix) => ({
                value: phonePrefix.code,
                label: getPhonePrefixDisplayLabel(phonePrefix),
                filterValue: getPhonePrefixFilterValue(phonePrefix),
            })),
        [normalizedPrefixes],
    );

    const prefixByCountryCode = useMemo(
        () => new Map(normalizedPrefixes.map((phonePrefix) => [phonePrefix.code, phonePrefix])),
        [normalizedPrefixes],
    );

    const shouldBeReadonly = readonly || normalizedPrefixes.length === 1;
    const activePhonePrefix =
        prefixByCountryCode.get(countryCode) ??
        normalizedPrefixes.find((phonePrefix) => phonePrefix.dialCode === normalizedDialCode) ??
        (shouldBeReadonly ? normalizedPrefixes[0] : undefined);
    const activeOption = activePhonePrefix
        ? (selectOptions.find((option) => option.value === activePhonePrefix.code) ?? null)
        : null;
    const activePrefixLabel = activePhonePrefix ? getPhonePrefixDisplayLabel(activePhonePrefix) : undefined;
    const comboBoxSearchValue = isDropdownOpen ? searchQuery : (activePrefixLabel ?? '');

    // Keep the hidden form fields aligned with the displayed selection.
    useEffect(() => {
        if (normalizedPrefixes.length === 0) {
            return;
        }

        const syncedPhonePrefix = activePhonePrefix ?? normalizedPrefixes[0];

        if (countryCode !== syncedPhonePrefix.code || normalizedDialCode !== syncedPhonePrefix.dialCode) {
            onChange({ countryCode: syncedPhonePrefix.code, dialCode: syncedPhonePrefix.dialCode });
        }
    }, [activePhonePrefix, countryCode, normalizedDialCode, normalizedPrefixes, onChange]);

    const handleDropdownOpenChange = useCallback((open: boolean) => {
        if (open) {
            setSearchQuery('');
        }
        setIsDropdownOpen(open);
    }, []);

    const handleSetSearchValue = useCallback(
        (value: string) => {
            if (isDropdownOpen) {
                setSearchQuery(value);
            }
        },
        [isDropdownOpen],
    );

    if (shouldBeReadonly) {
        return (
            <TextInput
                type="text"
                value={activePrefixLabel ?? ''}
                disabled={true}
                label={label}
                aria-describedby={describedBy}
                aria-invalid={hasError || undefined}
                aria-label={ariaLabel}
                className={twMergeCustom(selectClassName, className)}
                id="readonly-dial-code"
            />
        );
    }

    return (
        <Select
            activeOption={activeOption}
            ariaLabel={ariaLabel}
            className={className}
            externalSetIsSelectOpen={handleDropdownOpenChange}
            isDisabled={isDisabled}
            isRequired={isRequired}
            label={label}
            listClassName="right-auto min-w-full w-max [&_li>div]:whitespace-nowrap"
            options={selectOptions}
            selectClassName={selectClassName}
            tid={tid}
            comboBoxConfig={{
                inputAriaDescribedBy: describedBy,
                inputAriaInvalid: hasError,
                inputAriaLabel: typeof label === 'string' ? label : ariaLabel,
                searchValue: comboBoxSearchValue,
                setSearchValue: handleSetSearchValue,
                searchInputClassName: 'pr-0',
            }}
            onSelectOption={(option) => {
                const selectedPrefix = prefixByCountryCode.get(option.value);
                if (selectedPrefix) {
                    onChange({ countryCode: selectedPrefix.code, dialCode: selectedPrefix.dialCode });
                }
            }}
        />
    );
};
