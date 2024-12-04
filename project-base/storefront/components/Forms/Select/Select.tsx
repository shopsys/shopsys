'use client';

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
            <div className={twMergeCustom('relative w-full ', className)} ref={wrapperRef}>
                <div
                    className={twMergeCustom(
                        'group flex h-14 rounded-md border-2 border-inputBorder bg-inputBackground text-inputText hover:border-inputBorderHovered',
                        isOpen && 'rounded-b-none',
                        (isDisabled || isLoading) &&
                            'pointer-events-none cursor-no-drop border-inputBorderDisabled bg-inputBackgroundDisabled text-inputTextDisabled',
                        selectClassName,
                    )}
                >
                    {comboBoxConfig ? (
                        <>
                            <input
                                id={tid}
                                placeholder={placeholder}
                                tid={tid}
                                value={comboBoxConfig.searchValue}
                                className={twJoin(
                                    'h-full w-full bg-transparent px-3 !text-base focus-visible:outline-none',
                                    'placeholder:text-inputPlaceholder placeholder:hover:text-inputPlaceholderHovered placeholder:focus:text-inputPlaceholderActive placeholder:disabled:text-inputPlaceholderDisabled',
                                    comboBoxConfig.searchInputClassName,
                                )}
                                onChange={(e) => comboBoxConfig.setSearchValue(e.target.value)}
                                onClick={() => onSelectToggleOpenHandler(true)}
                            />

                            {activeOption?.count !== undefined && (
                                <span className="flex items-center whitespace-nowrap font-normal">
                                    ({activeOption.count})
                                </span>
                            )}
                        </>
                    ) : (
                        <button
                            className="w-full px-3 pt-5 text-left focus-visible:outline-none"
                            disabled={isDisabled}
                            id={tid}
                            tid={tid}
                            type="button"
                            onClick={() => onSelectToggleOpenHandler(!isOpen)}
                        >
                            <div
                                className={twJoin(
                                    'absolute font-secondary text-inputPlaceholder transition-all group-hover:text-inputPlaceholderHovered',
                                    isOpen || activeOption
                                        ? 'top-[9px] text-sm'
                                        : 'top-1/2 -translate-y-1/2 text-base font-semibold',
                                )}
                            >
                                {label}

                                {isRequired && <span className="ml-1 text-textError">*</span>}
                            </div>

                            {activeOption?.label && (
                                <div className="font-secondary font-semibold text-inputText">{activeOption.label}</div>
                            )}
                        </button>
                    )}

                    {isLoading && (
                        <div className="mx-1 flex items-center">
                            <SpinnerIcon className="size-5" />
                        </div>
                    )}

                    {onResetSelect && activeOption && !isLoading && (
                        <button type="reset" onClick={onResetSelect}>
                            <RemoveIcon className="hover:text-red mx-1 size-4 transition active:scale-95" />
                        </button>
                    )}

                    <button
                        className="pr-3 focus-visible:outline-none"
                        disabled={isDisabled}
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
