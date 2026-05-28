import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { RemoveBoldIcon } from 'components/Basic/Icon/RemoveBoldIcon';
import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { Tag } from 'components/Basic/Tag/Tag';
import { SelectList } from 'components/Forms/Select/SelectList';
import { ReactNode, useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { SelectOptionType } from 'types/selectOptions';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import useClickClosePopup from 'utils/ui/useClickClosePopup';

type MultiSelectProps<T = string> = {
    label?: string | ReactNode;
    options: SelectOptionType<T>[];
    selectedOptions: SelectOptionType<T>[];
    onChange: (selected: SelectOptionType<T>[]) => void;
    isDisabled?: boolean;
    isLoading?: boolean;
    isRequired?: boolean;
    hasError?: boolean;
    className?: string;
    listClassName?: string;
    tid?: string;
    tooltip?: ReactNode;
    comboBoxConfig?: {
        searchValue: string;
        setSearchValue: (value: string) => void;
        searchInputClassName?: string;
        placeholder: string;
    };
    inputWrapperClassName?: string;
    inputClassName?: string;
};

const toggleOption = <T,>(current: SelectOptionType<T>[], option: SelectOptionType<T>): SelectOptionType<T>[] => {
    const exists = current.some((o) => o.value === option.value);

    return exists ? current.filter((o) => o.value !== option.value) : [...current, option];
};

export function MultiSelect<T extends string | number = string>({
    label,
    options,
    selectedOptions,
    onChange,
    isDisabled,
    isLoading,
    isRequired,
    hasError,
    className,
    listClassName,
    tid,
    tooltip,
    comboBoxConfig,
    inputWrapperClassName,
    inputClassName,
}: MultiSelectProps<T>) {
    const { t } = useTranslation();

    const ref = useRef<HTMLDivElement>(null);
    const [isOpen, setIsOpen] = useState(false);
    const inputRef = useRef<HTMLInputElement>(null);

    useClickClosePopup([ref], () => setIsOpen(false));

    const handleOptionClick = (option: SelectOptionType<T>, m?: React.MouseEvent, k?: React.KeyboardEvent) => {
        m?.stopPropagation();
        k?.stopPropagation();

        if (comboBoxConfig) {
            document.getElementById('multiselect-search-input')?.focus();
        }

        onChange(toggleOption(selectedOptions, option));
        setIsOpen(true);
    };

    const handleRemoveSelected = (option: SelectOptionType<T>) => {
        onChange(selectedOptions.filter((o) => o.value !== option.value));
    };

    const filteredOptions = comboBoxConfig
        ? options.filter(
              (option) =>
                  option.label.toLowerCase().includes(comboBoxConfig.searchValue.toLowerCase()) &&
                  !selectedOptions.some((selected) => selected.value === option.value),
          )
        : options.filter((option) => !selectedOptions.some((selected) => selected.value === option.value));

    const handleSearchValueChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        comboBoxConfig?.setSearchValue(e.target.value);
        setIsOpen(true);
    };

    const handleSearchInputClick = (e: React.MouseEvent<HTMLInputElement>) => {
        e.stopPropagation();
        setIsOpen(true);
    };

    const handleArrowIconClick = () => {
        setIsOpen((prev) => !prev);
        if (inputRef.current) {
            inputRef.current.focus();
        }
    };

    return (
        <div className={twMergeCustom('flex w-full flex-col gap-4', className)}>
            {!comboBoxConfig && tooltip && <div className="mr-4 flex items-center text-sm lg:text-md">{tooltip}</div>}
            <div className={twMergeCustom('relative w-full', inputWrapperClassName)} ref={ref}>
                <div
                    className={twMergeCustom(
                        'group flex h-14 justify-between rounded-md border-2 border-input-border-default bg-input-bg-default text-input-text-default hover:border-input-border-hovered',
                        isOpen && 'justify-between rounded-b-none border-input-border-active',
                        (isDisabled || isLoading) &&
                            'pointer-events-none cursor-no-drop border-input-border-disabled bg-input-bg-disabled text-input-text-disabled',
                        hasError && 'border-input-border-error hover:border-input-border-error',
                        inputClassName,
                    )}
                >
                    {comboBoxConfig ? (
                        <>
                            <input
                                data-tid={tid}
                                id="multiselect-search-input"
                                placeholder={comboBoxConfig.placeholder}
                                ref={inputRef}
                                value={comboBoxConfig.searchValue}
                                className={twJoin(
                                    'size-full bg-transparent px-3 text-md! outline-hidden',
                                    'placeholder:text-input-placeholder-default placeholder:hover:text-input-placeholder-hovered placeholder:focus:text-input-placeholder-active placeholder:disabled:text-input-placeholder-disabled',
                                    comboBoxConfig.searchInputClassName,
                                )}
                                onChange={(e) => handleSearchValueChange(e)}
                                onClick={handleSearchInputClick}
                            />
                            <span
                                className={twJoin(
                                    'pointer-events-none absolute font-secondary text-input-placeholder-default transition-all group-hover:text-input-placeholder-hovered',
                                    isOpen || comboBoxConfig.searchValue || selectedOptions.length > 0
                                        ? 'top-[9px] text-sm'
                                        : 'top-1/2 left-3 -translate-y-1/2 font-semibold text-md',
                                )}
                            >
                                {label}
                                {isRequired && <span className="ml-1 text-text-error">*</span>}
                            </span>
                        </>
                    ) : (
                        <button
                            className="w-full cursor-pointer px-3 pt-5 text-left outline-hidden"
                            data-tid={tid}
                            disabled={isDisabled}
                            tabIndex={-1}
                            type="button"
                            onClick={() => setIsOpen((prev) => !prev)}
                        >
                            <span
                                className={twJoin(
                                    'absolute font-secondary text-input-placeholder-default transition-all group-hover:text-input-placeholder-hovered',
                                    isOpen || selectedOptions.length > 0
                                        ? 'top-[9px] text-sm'
                                        : 'top-1/2 -translate-y-1/2 font-semibold text-md',
                                )}
                            >
                                {label}
                                {isRequired && <span className="ml-1 text-text-error">*</span>}
                            </span>

                            {selectedOptions.length > 0 && (
                                <span className="whitespace-nowrap font-secondary font-semibold text-input-text-default">
                                    {`${selectedOptions.length} selected`}
                                </span>
                            )}
                        </button>
                    )}

                    {isLoading && (
                        <div className="mx-1 flex items-center">
                            <SpinnerIcon className="size-5" />
                        </div>
                    )}

                    <button
                        className="cursor-pointer rounded-sm px-3"
                        disabled={isDisabled}
                        tabIndex={0}
                        type="button"
                        onClick={handleArrowIconClick}
                    >
                        <ArrowIcon className={twJoin('size-5 transition', isOpen ? 'rotate-180' : 'rotate-0')} />
                    </button>
                </div>

                {isOpen && (
                    <SelectList
                        listClassName={listClassName}
                        options={filteredOptions}
                        setIsOpen={setIsOpen}
                        onSelectOption={(option, e, k) => handleOptionClick(option, e, k)}
                    />
                )}

                {selectedOptions.length > 0 && (
                    <div className={twJoin('flex flex-wrap items-center gap-x-1.5 gap-y-2 pt-2')}>
                        {selectedOptions.map((selected) => (
                            <Tag
                                key={selected.value}
                                ariaLabel={t('Remove', { ns: 'accessibility' })}
                                className="group/tag"
                                onClick={() => handleRemoveSelected(selected)}
                            >
                                {selected.label}

                                <RemoveBoldIcon className="ml-2 w-3 cursor-pointer text-icon-less group-hover/tag:text-icon-inverted" />
                            </Tag>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}
