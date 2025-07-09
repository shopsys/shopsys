import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { ReactNode, useEffect, useRef, useState } from 'react';
import InfiniteScroll, { Props as InfiniteScrollProps } from 'react-infinite-scroll-component';
import { twJoin } from 'tailwind-merge';
import { FunctionComponentProps } from 'types/globals';
import { SelectOptionType } from 'types/selectOptions';
import { twMergeCustom } from 'utils/twMerge';

export type SelectListProps<T = string> = {
    itemBeforeText?: ReactNode;
    itemAfterText?: ReactNode;
    options: SelectOptionType<T>[];
    onSelectOption: (data: SelectOptionType<T>) => void;
    activeOption?: SelectOptionType<T> | null;
    infinityScrollConfig?: Pick<InfiniteScrollProps, 'hasMore' | 'next' | 'dataLength'> & { pageSize: number };
    listClassName?: string;
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
}: SelectListProps<T> & FunctionComponentProps) => {
    const [focusedIndex, setFocusedIndex] = useState<number | null>(0);
    const listRef = useRef<HTMLUListElement>(null);

    useEffect(() => {
        if (focusedIndex !== null && listRef.current) {
            const focusedElement = listRef.current.children[focusedIndex] as HTMLElement;
            focusedElement.focus();
        }
    }, [focusedIndex]);

    const handleKeyDown = (e: React.KeyboardEvent) => {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            setFocusedIndex((prevIndex) => (prevIndex === null ? 0 : Math.min(prevIndex + 1, options.length - 1)));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setFocusedIndex((prevIndex) => (prevIndex === null ? options.length - 1 : Math.max(prevIndex - 1, 0)));
        } else if (e.key === 'Enter' && focusedIndex !== null && !options[focusedIndex].isDisabled) {
            onSelectOption(options[focusedIndex]);
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
            onClick={!option.isDisabled ? () => onSelectOption(option) : undefined}
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
            <AnimateCollapseDiv
                className="z-above border-input-border-default bg-background-default hover:border-input-border-hovered absolute right-0 left-0 !block rounded-b-md border-2 border-t-0"
                keyName={tid}
            >
                <InfiniteScroll
                    dataLength={infinityScrollConfig.dataLength}
                    hasMore={infinityScrollConfig.hasMore}
                    height={200}
                    next={infinityScrollConfig.next}
                    loader={
                        <>
                            <div className="flex h-9 items-center pl-3">
                                <Skeleton className="h-4 w-32" />
                            </div>
                            <div className="flex h-9 items-center pl-3">
                                <Skeleton className="h-4 w-32" />
                            </div>
                        </>
                    }
                >
                    <ul ref={listRef}>{SelectListItems}</ul>
                </InfiniteScroll>
            </AnimateCollapseDiv>
        );
    }

    return (
        <AnimateCollapseDiv
            keyName={tid}
            className={twMergeCustom(
                '!overflow-y-auto',
                'z-above bg-background-default absolute right-0 left-0 !block max-h-[144px] rounded-b-md lg:max-h-[200px]',
                'border-input-border-default hover:border-input-border-hovered border-2 border-t-0',
                '[&::-webkit-scrollbar-thumb]:bg-input-placeholder-default [&::-webkit-scrollbar]:h-[0px] [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full',
                listClassName,
            )}
        >
            <ul ref={listRef}>{SelectListItems}</ul>
        </AnimateCollapseDiv>
    );
};
