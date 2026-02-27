import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
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
    options: SelectOptionType<T>[];
    onSelectOption: (data: SelectOptionType<T>, m?: React.MouseEvent, k?: React.KeyboardEvent) => void;
    activeOption?: SelectOptionType<T> | null;
    infinityScrollConfig?: Pick<InfiniteScrollProps, 'hasMore' | 'next' | 'dataLength'> & { pageSize: number };
    listClassName?: string;
    setIsOpen?: (isOpen: boolean) => void;
};

export const SelectList = <T extends string | number | undefined | Record<any, any> | null | boolean = string>({
    tid,
    options,
    onSelectOption,
    itemAfterText,
    itemBeforeText,
    activeOption,
    infinityScrollConfig,
    listClassName,
    setIsOpen,
}: SelectListProps<T> & FunctionComponentProps) => {
    const [focusedIndex, setFocusedIndex] = useState<number | null>(null);
    const listRef = useRef<HTMLUListElement>(null);
    const optionsLength = options.length;

    useFocusTrap(listRef);

    const onKeyDown = useEffectEvent((k: KeyboardEvent) => {
        if (k.key === 'Escape') {
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
        } else if (
            k.key === 'Enter' &&
            focusedIndex !== null &&
            focusedIndex < options.length &&
            !options[focusedIndex]?.isDisabled
        ) {
            onSelectOption(options[focusedIndex], undefined, k);
            setFocusedIndex(null);
        }
    };

    const SelectListItems = options.map((option, index) => (
        <li
            key={option.label}
            aria-selected={option.value === activeOption?.value}
            data-tid={`${tid}${index}`}
            role="option"
            tabIndex={option.isDisabled ? -1 : 0}
            className={twMergeCustom(
                'hover:bg-input-bg-hovered list-none font-semibold outline-hidden',
                option.isDisabled && 'bg-input-bg-disabled text-input-text-disabled pointer-events-none cursor-no-drop',
                'focus-visible:text-text-default focus-visible:bg-orange-500',
            )}
            onClick={!option.isDisabled ? (e) => onSelectOption(option, e) : undefined}
            onFocus={() => setFocusedIndex(index)}
            onKeyDown={(e) => handleKeyDown(e)}
        >
            <div
                className={twJoin(
                    'font-secondary hover:text-input-text-hovered hover:bg-fill-accent-less flex w-full cursor-pointer items-center justify-between gap-2 p-3',
                    option.value === activeOption?.value && 'text-input-text-active',
                    option.isDisabled && 'text-input-text-disabled',
                )}
            >
                {itemBeforeText && itemBeforeText}

                {option.label}

                {option.count !== undefined && (
                    <span className="font-secondary text-input-placeholder-default font-normal whitespace-nowrap">
                        ({option.count})
                    </span>
                )}
            </div>

            {itemAfterText && itemAfterText}
        </li>
    ));

    if (infinityScrollConfig && infinityScrollConfig.dataLength >= infinityScrollConfig.pageSize) {
        return (
            <SelectListInfiniteScroll infinityScrollConfig={infinityScrollConfig} listRef={listRef} tid={tid}>
                {SelectListItems}
            </SelectListInfiniteScroll>
        );
    }

    return (
        <AnimateCollapseDiv
            keyName={tid}
            className={twMergeCustom(
                'overflow-y-auto!',
                'z-above bg-background-default absolute right-0 left-0 block! max-h-36 rounded-b-md lg:max-h-[200px]',
                'border-input-border-default hover:border-input-border-hovered border-2 border-t-0',
                '[&::-webkit-scrollbar-thumb]:bg-input-placeholder-default [&::-webkit-scrollbar]:h-0 [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full',
                listClassName,
            )}
        >
            <ul ref={listRef}>{SelectListItems}</ul>
        </AnimateCollapseDiv>
    );
};
