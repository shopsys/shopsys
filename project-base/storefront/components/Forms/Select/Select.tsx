import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { CloseIcon } from 'components/Basic/Icon/CloseIcon';
import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { IconButton } from 'components/Forms/Button/IconButton';
import { AnimatePresence } from 'framer-motion';
import { ReactElement, ReactNode, useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { FunctionComponentProps } from 'types/globals';
import { SelectOptionType } from 'types/selectOptions';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import useClickClosePopup from 'utils/ui/useClickClosePopup';
import { SelectList, SelectListProps } from './SelectList';

type SelectProps<T = string> = {
    ariaLabel: string;
    ariaDescribedBy?: string;
    ariaInvalid?: boolean;
    label?: string | ReactNode;
    placeholder?: string;
    selectClassName?: string;
    isDisabled?: boolean;
    isLoading?: boolean;
    onSelectOption: (data: SelectOptionType<T>) => void;
    renderAdditionalItem?: (
        isOpen: boolean,
        setIsOpen: (isOpen: boolean) => void,
        isDisabled?: boolean,
        activeOption?: SelectOptionType<T> | null,
    ) => ReactElement<any, any> | null;
    isRequired?: boolean;
    comboBoxConfig?: {
        searchValue: string;
        setSearchValue: (searchValue: string) => void;
        inputAriaDescribedBy?: string;
        inputAriaInvalid?: boolean;
        inputAriaLabel?: string;
        searchInputClassName?: string;
        filterOptions?: boolean;
    };
    onResetSelect?: () => void;
    externalSetIsSelectOpen?: (isOpen: boolean) => void;
    listClassName?: string;
} & SelectListProps<T>;

export const Select = <T extends string | number | undefined | Record<any, any> | null | boolean = string>({
    ariaLabel,
    ariaDescribedBy,
    ariaInvalid,
    label,
    options,
    onSelectOption,
    placeholder,
    isDisabled,
    itemAfterText,
    itemBeforeText,
    activeOption,
    className,
    renderAdditionalItem,
    selectClassName,
    isRequired,
    tid,
    comboBoxConfig,
    isLoading,
    infinityScrollConfig,
    onResetSelect,
    externalSetIsSelectOpen,
    listClassName,
}: SelectProps<T> & FunctionComponentProps) => {
    const { t } = useTranslation();
    const wrapperRef = useRef(null);
    const additionalItemRef = useRef(null);
    const [isOpen, setIsOpen] = useState(false);
    const listboxId = `${tid}-listbox`;

    const onSelectToggleOpenHandler = (isOpenFromArguments: boolean) => {
        externalSetIsSelectOpen?.(isOpenFromArguments);
        setIsOpen(isOpenFromArguments);
    };

    useClickClosePopup([wrapperRef, additionalItemRef], () => {
        onSelectToggleOpenHandler(false);
    });

    const onSelectOptionExtended = (option: SelectOptionType<T>) => {
        onSelectToggleOpenHandler(false);
        onSelectOption(option);
    };

    const handleSearchValueChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        comboBoxConfig?.setSearchValue(e.target.value);
        setIsOpen(true);
    };

    const filteredOptions = comboBoxConfig
        ? comboBoxConfig.filterOptions === false
            ? options
            : options.filter((option) =>
                  (option.filterValue ?? option.label).toLowerCase().includes(comboBoxConfig.searchValue.toLowerCase()),
              )
        : options;

    return (
        <>
            <div className={twMergeCustom('relative w-full', className)} ref={wrapperRef}>
                <div
                    className={twMergeCustom(
                        'group flex h-14 rounded-md border-2 border-input-border-default bg-input-bg-default pr-1 text-input-text-default hover:border-input-border-hovered',
                        isOpen && 'rounded-b-none border-input-border-active',
                        (isDisabled || isLoading) &&
                            'pointer-events-none cursor-no-drop border-input-border-disabled bg-input-bg-disabled text-input-text-disabled',
                        selectClassName,
                    )}
                >
                    {comboBoxConfig ? (
                        <>
                            <input
                                aria-autocomplete="list"
                                aria-controls={listboxId}
                                aria-describedby={comboBoxConfig.inputAriaDescribedBy}
                                aria-expanded={isOpen}
                                aria-haspopup="listbox"
                                aria-invalid={comboBoxConfig.inputAriaInvalid}
                                aria-label={comboBoxConfig.inputAriaLabel ?? ariaLabel}
                                // Opt out of password-manager autofill — search/combobox inputs should
                                // never receive it (vendor-specific data-* attributes for LP/1P/BW/Dashlane).
                                autoComplete="off"
                                data-1p-ignore="true"
                                data-bwignore="true"
                                data-form-type="other"
                                data-lpignore="true"
                                data-tid={tid}
                                id={tid}
                                placeholder={placeholder}
                                // Input mounts only when the dropdown opens, so focusing on mount
                                // lands the cursor into the search field right after the user opens it.
                                ref={(node) => {
                                    node?.focus();
                                }}
                                role="combobox"
                                value={comboBoxConfig.searchValue}
                                className={twMergeCustom(
                                    'h-full w-full min-w-0 bg-transparent px-3 font-secondary font-semibold text-md! outline-hidden',
                                    label && 'pt-5',
                                    'placeholder:text-input-placeholder-default placeholder:hover:text-input-placeholder-hovered placeholder:focus:text-input-placeholder-active placeholder:disabled:text-input-placeholder-disabled',
                                    comboBoxConfig.searchInputClassName,
                                )}
                                onChange={(e) => handleSearchValueChange(e)}
                                onClick={() => onSelectToggleOpenHandler(true)}
                            />

                            {label && (
                                <span
                                    className={twJoin(
                                        'pointer-events-none absolute font-secondary text-input-placeholder-default transition-all group-hover:text-input-placeholder-hovered',
                                        isOpen || comboBoxConfig.searchValue || activeOption
                                            ? 'top-2.25 left-3 text-sm'
                                            : 'top-1/2 left-3 -translate-y-1/2 font-semibold text-md',
                                    )}
                                >
                                    {label}

                                    {isRequired && <span className="ml-1 text-text-error">*</span>}
                                </span>
                            )}

                            {activeOption?.count !== undefined && (
                                <span className="flex items-center whitespace-nowrap font-normal">
                                    ({activeOption.count})
                                </span>
                            )}
                        </>
                    ) : (
                        <button
                            aria-describedby={ariaDescribedBy}
                            aria-invalid={ariaInvalid}
                            className="w-full min-w-0 cursor-pointer px-3 pt-5 text-left outline-hidden"
                            data-tid={tid}
                            disabled={isDisabled}
                            id={tid}
                            tabIndex={-1}
                            type="button"
                            onClick={() => onSelectToggleOpenHandler(!isOpen)}
                        >
                            <span
                                className={twJoin(
                                    'absolute font-secondary text-input-placeholder-default transition-all group-hover:text-input-placeholder-hovered',
                                    isOpen || activeOption
                                        ? 'top-2.25 text-sm'
                                        : 'top-1/2 -translate-y-1/2 font-semibold text-md',
                                )}
                            >
                                {label}

                                {isRequired && <span className="ml-1 text-text-error">*</span>}
                            </span>

                            {activeOption?.label && (
                                <span
                                    className={twJoin(
                                        'block truncate font-secondary font-semibold',
                                        isDisabled ? 'text-input-text-disabled' : 'text-input-text-default',
                                    )}
                                >
                                    {activeOption.label}
                                </span>
                            )}
                        </button>
                    )}

                    {isLoading && (
                        <div className="mx-1 flex items-center">
                            <SpinnerIcon
                                aria-label={t('Loading options', { ns: 'accessibility' })}
                                className="size-5"
                            />
                        </div>
                    )}

                    {onResetSelect && activeOption && !isLoading && (
                        <IconButton
                            Icon={CloseIcon}
                            ariaLabel={t('Clear selected option', { ns: 'accessibility' })}
                            className="self-center"
                            shape="rounded"
                            size="compact"
                            title={t('Clear selected option', { ns: 'accessibility' })}
                            tooltipLabel={t('Clear selected option', { ns: 'accessibility' })}
                            type="reset"
                            variant="ghost"
                            onClick={onResetSelect}
                        />
                    )}

                    <IconButton
                        Icon={ArrowIcon}
                        aria-describedby={ariaDescribedBy}
                        aria-expanded={isOpen}
                        aria-haspopup="listbox"
                        aria-invalid={ariaInvalid}
                        ariaLabel={ariaLabel}
                        className="self-center"
                        disabled={isDisabled}
                        iconClassName={twJoin('transition-transform', isOpen ? 'rotate-180' : 'rotate-0')}
                        shape="rounded"
                        title={ariaLabel}
                        variant="ghost"
                        onClick={() => onSelectToggleOpenHandler(!isOpen)}
                    />
                </div>

                <AnimatePresence initial={false}>
                    {isOpen && (
                        <SelectList
                            activeOption={activeOption}
                            infinityScrollConfig={infinityScrollConfig}
                            itemAfterText={itemAfterText}
                            itemBeforeText={itemBeforeText}
                            listClassName={listClassName}
                            listId={listboxId}
                            options={filteredOptions}
                            setIsOpen={setIsOpen}
                            tid={tid}
                            onSelectOption={onSelectOptionExtended}
                        />
                    )}
                </AnimatePresence>
            </div>

            {renderAdditionalItem && (
                <div ref={additionalItemRef}>
                    {renderAdditionalItem(isOpen, onSelectToggleOpenHandler, isDisabled, activeOption)}
                </div>
            )}
        </>
    );
};

Select.displayName = 'Select';
