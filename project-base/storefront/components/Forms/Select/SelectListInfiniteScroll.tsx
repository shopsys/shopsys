import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { ReactNode, RefObject } from 'react';
import InfiniteScroll, { Props as InfiniteScrollProps } from 'react-infinite-scroll-component';

type SelectListInfiniteScrollProps = {
    infinityScrollConfig: Pick<InfiniteScrollProps, 'hasMore' | 'next' | 'dataLength'> & { pageSize: number };
    listRef: RefObject<HTMLUListElement | null>;
    children: ReactNode;
};

export const SelectListInfiniteScroll: FC<SelectListInfiniteScrollProps> = ({
    tid,
    infinityScrollConfig,
    listRef,
    children,
}) => {
    return (
        <AnimateCollapseDiv
            className="block! absolute right-0 left-0 z-above rounded-b-md border-2 border-input-border-default border-t-0 bg-background-default hover:border-input-border-hovered"
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
                <ul ref={listRef}>{children}</ul>
            </InfiniteScroll>
        </AnimateCollapseDiv>
    );
};
