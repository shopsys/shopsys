import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { ReactNode, RefObject } from 'react';
import InfiniteScroll, { Props as InfiniteScrollProps } from 'react-infinite-scroll-component';

type SelectListInfiniteScrollProps = {
    infinityScrollConfig: Pick<InfiniteScrollProps, 'hasMore' | 'next' | 'dataLength'> & { pageSize: number };
    listRef: RefObject<HTMLDivElement | null>;
    children: ReactNode;
    listId?: string;
};

export const SelectListInfiniteScroll: FC<SelectListInfiniteScrollProps> = ({
    tid,
    infinityScrollConfig,
    listRef,
    children,
    listId,
}) => {
    return (
        <AnimateCollapseDiv
            className="block! absolute right-0 left-0 z-above mt-1 overflow-hidden rounded-input border border-input-border-default bg-background-default shadow-lg"
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
                <div id={listId} ref={listRef} role="listbox">
                    {children}
                </div>
            </InfiniteScroll>
        </AnimateCollapseDiv>
    );
};
