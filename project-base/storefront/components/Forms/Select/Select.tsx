import { SelectList, SelectListProps } from './SelectList';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { AnimatePresence } from 'framer-motion';
import { ReactElement, ReactNode, useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { FunctionComponentProps } from 'types/globals';
import { SelectOptionType } from 'types/selectOptions';
import { twMergeCustom } from 'utils/twMerge';
import useClickClosePopup from 'utils/ui/useClickClosePopup';

export type SelectProps<T = string> = {
    ariaLabel: string;
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
        searchInputClassName?: string;
    };
    onResetSelect?: () => void;
    externalSetIsSelectOpen?: (isOpen: boolean) => void;
    listClassName?: string;
} & SelectListProps<T>;

export const Select = <T extends string | number | undefined | Record<any, any> | null | boolean = string>({
    ariaLabel,
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
    const wrapperRef = useRef(null);
    const additionalItemRef = useRef(null);
    const [isOpen, setIsOpen] = useState(false);

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

    const filteredOptions = comboBoxConfig
        ? options.filter((option) => option.label.toLowerCase().includes(comboBoxConfig.searchValue.toLowerCase()))
        : options;

    return (
        <>
            <div className={twMergeCustom('relative w-full', className)} ref={wrapperRef}>
                <div
                    className={twMergeCustom(
                        'border-input-border-default bg-input-bg-default text-input-text-default hover:border-input-border-hovered group flex h-14 rounded-md border-2',
                        isOpen && 'border-input-border-active rounded-b-none',
                        (isDisabled || isLoading) &&
                            'border-input-border-disabled bg-input-bg-disabled text-input-text-disabled pointer-events-none cursor-no-drop',
                        selectClassName,
                    )}
                >
                    {comboBoxConfig ? (
                        <>
                            <input
                                data-tid={tid}
                                id={tid}
                                placeholder={placeholder}
                                value={comboBoxConfig.searchValue}
                                className={twJoin(
                                    'h-full w-full bg-transparent px-3 !text-base outline-hidden',
                                    'placeholder:text-input-placeholder-default placeholder:hover:text-input-placeholder-hovered placeholder:focus:text-input-placeholder-active placeholder:disabled:text-input-placeholder-disabled',
                                    comboBoxConfig.searchInputClassName,
                                )}
                                onChange={(e) => comboBoxConfig.setSearchValue(e.target.value)}
                                onClick={() => onSelectToggleOpenHandler(true)}
                            />

                            {activeOption?.count !== undefined && (
                                <span className="flex items-center font-normal whitespace-nowrap">
                                    ({activeOption.count})
                                </span>
                            )}
                        </>
                    ) : (
                        <button
                            className="w-full cursor-pointer px-3 pt-5 text-left outline-hidden"
                            data-tid={tid}
                            disabled={isDisabled}
                            id={tid}
                            tabIndex={-1}
                            type="button"
                            onClick={() => onSelectToggleOpenHandler(!isOpen)}
                        >
                            <span
                                className={twJoin(
                                    'font-secondary text-input-placeholder-default group-hover:text-input-placeholder-hovered absolute transition-all',
                                    isOpen || activeOption
                                        ? 'top-[9px] text-sm'
                                        : 'top-1/2 -translate-y-1/2 text-base font-semibold',
                                )}
                            >
                                {label}

                                {isRequired && <span className="text-text-error ml-1">*</span>}
                            </span>

                            {activeOption?.label && (
                                <span className="font-secondary text-input-text-default font-semibold">
                                    {activeOption.label}
                                </span>
                            )}
                        </button>
                    )}

                    {isLoading && (
                        <div className="mx-1 flex items-center">
                            <SpinnerIcon className="size-5" />
                        </div>
                    )}

                    {onResetSelect && activeOption && !isLoading && (
                        <button className="cursor-pointer" tabIndex={0} type="reset" onClick={onResetSelect}>
                            <RemoveIcon className="hover:text-red mx-1 size-4 transition active:scale-95" />
                        </button>
                    )}

                    <button
                        className="rounded-sm px-3"
                        disabled={isDisabled}
                        tabIndex={0}
                        title={ariaLabel}
                        type="button"
                        onClick={() => onSelectToggleOpenHandler(!isOpen)}
                    >
                        <ArrowIcon className={twJoin('size-5 transition', isOpen ? 'rotate-180' : 'rotate-0')} />
                    </button>
                </div>

                <AnimatePresence initial={false}>
                    {isOpen && (
                        <SelectList
                            activeOption={activeOption}
                            infinityScrollConfig={infinityScrollConfig}
                            itemAfterText={itemAfterText}
                            itemBeforeText={itemBeforeText}
                            listClassName={listClassName}
                            options={filteredOptions}
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
