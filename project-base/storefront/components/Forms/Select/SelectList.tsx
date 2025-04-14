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
            setFocusedIndex((prevIndex) => (prevIndex === null ? 0 : Math.min(prevIndex + 1, options.length - 1)));
        } else if (e.key === 'ArrowUp') {
            setFocusedIndex((prevIndex) => (prevIndex === null ? options.length - 1 : Math.max(prevIndex - 1, 0)));
        } else if (e.key === 'Enter' && focusedIndex !== null && !options[focusedIndex].isDisabled) {
            onSelectOption(options[focusedIndex]);
        }
    };

    const SelectListItems = options.map((option, index) => (
        <li
            key={option.label}
            tabIndex={option.isDisabled ? -1 : 0}
            tid={`${tid}${index}`}
            className={twMergeCustom(
                'hover:bg-inputBackgroundHovered list-none font-semibold focus-visible:outline-hidden',
                option.isDisabled &&
                    'bg-inputBackgroundDisabled text-inputTextDisabled pointer-events-none cursor-no-drop',
                'focus:bg-inputBackgroundHovered',
            )}
            onClick={!option.isDisabled ? () => onSelectOption(option) : undefined}
            onKeyDown={(e) => handleKeyDown(e)}
        >
            <button
                type="button"
                className={twJoin(
                    'font-secondary flex w-full items-center justify-between gap-2 p-3',
                    option.value === activeOption?.value && 'text-inputTextActive',
                    option.isDisabled && 'text-inputTextDisabled',
                )}
            >
                {itemBeforeText && itemBeforeText}

                {option.label}

                {option.count !== undefined && (
                    <span className="font-secondary text-inputPlaceholder font-normal whitespace-nowrap">
                        ({option.count})
                    </span>
                )}
            </button>

            {itemAfterText && itemAfterText}
        </li>
    ));

    if (infinityScrollConfig && infinityScrollConfig.dataLength >= infinityScrollConfig.pageSize) {
        return (
            <AnimateCollapseDiv
                className="z-above border-inputBorder bg-background hover:border-inputBorderHovered absolute right-0 left-0 !block rounded-b-md border-2 border-t-0"
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
                'z-above bg-background absolute right-0 left-0 !block max-h-[144px] rounded-b-md lg:max-h-[200px]',
                'border-inputBorder hover:border-inputBorderHovered border-2 border-t-0',
                '[&::-webkit-scrollbar-thumb]:bg-inputPlaceholder [&::-webkit-scrollbar]:h-[0px] [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full',
                listClassName,
            )}
        >
            <ul ref={listRef}>{SelectListItems}</ul>
        </AnimateCollapseDiv>
    );
};
