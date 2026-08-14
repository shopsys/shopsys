import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { useIsPresent } from 'framer-motion';
import dynamic from 'next/dynamic';
import { ReactNode, useEffect, useEffectEvent, useRef, useState } from 'react';
import type { Props as InfiniteScrollProps } from 'react-infinite-scroll-component';
import { twJoin } from 'tailwind-merge';
import { FunctionComponentProps } from 'types/globals';
import { SelectOptionType } from 'types/selectOptions';
import { twMergeCustom } from 'utils/twMerge';
import { useFocusTrap } from 'utils/useFocusTrap';

const SelectListInfiniteScroll = dynamic(
    () => import('components/Forms/Select/SelectListInfiniteScroll').then((mod) => mod.SelectListInfiniteScroll),
    { ssr: false },
);

export type SelectListProps<T = string> = {
    itemBeforeText?: ReactNode;
    itemAfterText?: ReactNode;
    listId?: string;
    options: SelectOptionType<T>[];
    onSelectOption: (data: SelectOptionType<T>, m?: React.MouseEvent, k?: React.KeyboardEvent) => void;
    activeOption?: SelectOptionType<T> | null;
    infinityScrollConfig?: Pick<InfiniteScrollProps, 'hasMore' | 'next' | 'dataLength'> & { pageSize: number };
    listClassName?: string;
    renderOption?: (option: SelectOptionType<T>) => ReactNode;
    initialFocusedIndex?: number;
    setIsOpen?: (isOpen: boolean) => void;
};

export const SelectList = <T extends string | number | undefined | Record<any, any> | null | boolean = string>({
    tid,
    options,
    onSelectOption,
    itemAfterText,
    itemBeforeText,
    listId,
    activeOption,
    infinityScrollConfig,
    listClassName,
    setIsOpen,
    renderOption,
    initialFocusedIndex,
}: SelectListProps<T> & FunctionComponentProps) => {
    const [focusedIndex, setFocusedIndex] = useState<number | null>(initialFocusedIndex ?? null);
    const listRef = useRef<HTMLDivElement>(null);
    const returnFocusRef = useRef<HTMLElement | null>(null);
    const isPresent = useIsPresent();
    const optionsLength = options.length;

    useFocusTrap(isPresent ? listRef : undefined);

    useEffect(() => {
        returnFocusRef.current ??= document.activeElement instanceof HTMLElement ? document.activeElement : null;
    }, []);

    useEffect(() => {
        // The exit animation keeps the list mounted after closing; release focus before it disappears.
        if (!isPresent && listRef.current?.contains(document.activeElement)) {
            returnFocusRef.current?.focus();
        }
    }, [isPresent]);

    const onKeyDown = useEffectEvent((k: KeyboardEvent) => {
        if (!isPresent) {
            return;
        }

        if (k.key === 'Escape') {
            // A select inside a popup must close before the popup's window-level Escape handler runs.
            k.stopPropagation();
            setIsOpen?.(false);
            setFocusedIndex(null);
            return;
        }

        if (k.key === 'ArrowDown' && focusedIndex === null && optionsLength > 0) {
            k.preventDefault();
            setFocusedIndex(0);
        }

        if (k.key === 'ArrowUp' && focusedIndex === null && optionsLength > 0) {
            k.preventDefault();
            setFocusedIndex(optionsLength - 1);
        }
    });

    useEffect(() => {
        if (focusedIndex !== null && listRef.current && focusedIndex < listRef.current.children.length) {
            const focusedElement = listRef.current.children[focusedIndex] as HTMLElement;
            focusedElement.focus();
        }
    }, [focusedIndex]);

    useEffect(() => {
        document.addEventListener('keydown', onKeyDown);

        return () => document.removeEventListener('keydown', onKeyDown);
    }, []);

    const handleKeyDown = (k: React.KeyboardEvent<Element>) => {
        if (options.length === 0) {
            return;
        }

        if (k.key === 'ArrowDown') {
            k.preventDefault();
            setFocusedIndex((prevIndex) => (prevIndex === null ? 0 : Math.min(prevIndex + 1, options.length - 1)));
        } else if (k.key === 'ArrowUp') {
            k.preventDefault();
            setFocusedIndex((prevIndex) => (prevIndex === null ? options.length - 1 : Math.max(prevIndex - 1, 0)));
        } else if (k.key === 'Home') {
            k.preventDefault();
            setFocusedIndex(0);
        } else if (k.key === 'End') {
            k.preventDefault();
            setFocusedIndex(options.length - 1);
        } else if (
            (k.key === 'Enter' || k.key === ' ') &&
            focusedIndex !== null &&
            focusedIndex < options.length &&
            !options[focusedIndex]?.isDisabled
        ) {
            k.preventDefault();
            onSelectOption(options[focusedIndex], undefined, k);
            setFocusedIndex(null);
        }
    };

    const SelectListItems = options.map((option, index) => (
        <div
            key={option.label}
            aria-selected={option.value === activeOption?.value}
            data-tid={`${tid}${index}`}
            id={`${tid}-option-${index}`}
            role="option"
            tabIndex={option.isDisabled ? -1 : 0}
            className={twMergeCustom(
                'list-none font-semibold outline-hidden hover:bg-input-bg-hovered',
                option.value === activeOption?.value && 'bg-fill-accent-less',
                option.isDisabled && 'pointer-events-none cursor-no-drop bg-input-bg-disabled text-input-text-disabled',
                'focus-visible:bg-orange-500 focus-visible:text-text-default',
            )}
            onClick={!option.isDisabled ? (e) => onSelectOption(option, e) : undefined}
            onFocus={() => setFocusedIndex(index)}
            onKeyDown={(e) => handleKeyDown(e)}
        >
            <div
                className={twJoin(
                    'flex w-full cursor-pointer items-center justify-between gap-2 p-3 font-secondary hover:bg-fill-accent-less hover:text-input-text-hovered',
                    option.value === activeOption?.value && 'text-input-text-active',
                    option.isDisabled && 'text-input-text-disabled',
                )}
            >
                {itemBeforeText && itemBeforeText}

                {renderOption ? renderOption(option) : option.label}

                {option.count !== undefined && (
                    <span className="whitespace-nowrap font-secondary text-input-placeholder-default">
                        ({option.count})
                    </span>
                )}

                {option.value === activeOption?.value && (
                    <CheckmarkIcon aria-hidden="true" className="size-4 shrink-0 text-icon-accent" />
                )}
            </div>

            {itemAfterText && itemAfterText}
        </div>
    ));

    if (infinityScrollConfig && infinityScrollConfig.dataLength >= infinityScrollConfig.pageSize) {
        return (
            <SelectListInfiniteScroll
                infinityScrollConfig={infinityScrollConfig}
                listId={listId}
                listRef={listRef}
                tid={tid}
            >
                {SelectListItems}
            </SelectListInfiniteScroll>
        );
    }

    return (
        <AnimateCollapseDiv
            keyName={tid}
            className={twMergeCustom(
                'overflow-y-auto!',
                'block! absolute right-0 left-0 z-above max-h-36 rounded-b-md bg-background-default lg:max-h-50',
                'border-2 border-input-border-default border-t-0 hover:border-input-border-hovered',
                '[&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-input-placeholder-default [&::-webkit-scrollbar]:h-0 [&::-webkit-scrollbar]:w-2',
                listClassName,
            )}
        >
            <div id={listId} ref={listRef} role="listbox">
                {SelectListItems}
            </div>
        </AnimateCollapseDiv>
    );
};
