import { Button } from 'components/Forms/Button/Button';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { Fragment, MouseEventHandler, RefObject, forwardRef } from 'react';
import { twJoin } from 'tailwind-merge';
import { getUrlQueriesWithoutDynamicPageQueries } from 'utils/parsing/getUrlQueriesWithoutDynamicPageQueries';
import { useCurrentLoadMoreQuery } from 'utils/queryParams/useCurrentLoadMoreQuery';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';
import { useUpdateLoadMoreQuery } from 'utils/queryParams/useUpdateLoadMoreQuery';
import { useUpdatePaginationQuery } from 'utils/queryParams/useUpdatePaginationQuery';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { usePagination } from 'utils/ui/usePagination';

type PaginationProps = {
    totalCount: number;
    paginationScrollTargetRef: RefObject<HTMLDivElement> | null;
    hasNextPage?: boolean;
    isWithLoadMore?: boolean;
    pageSize?: number;
    type?: 'default' | 'blog';
};

export const Pagination: FC<PaginationProps> = ({
    totalCount,
    paginationScrollTargetRef,
    hasNextPage,
    isWithLoadMore,
    pageSize = DEFAULT_PAGE_SIZE,
    type = 'defualt',
}) => {
    const router = useRouter();
    const isDesktop = useMediaMin('sm');
    const currentPage = useCurrentPageQuery();
    const currentLoadMore = useCurrentLoadMoreQuery();
    const updatePagination = useUpdatePaginationQuery();
    const loadMore = useUpdateLoadMoreQuery();
    const currentPageWithLoadMore = Math.min(currentPage + currentLoadMore, Math.ceil(totalCount / pageSize));
    const paginationButtons = usePagination(totalCount, currentPageWithLoadMore, !isDesktop, pageSize);
    const { t } = useTranslation();

    if (!paginationButtons || paginationButtons.length === 1) {
        return null;
    }

    const asPathWithoutQueryParams = router.asPath.split('?')[0];
    const queryParams = getUrlQueriesWithoutDynamicPageQueries(router.query);

    const onChangePage = (pageNumber: number) => () => {
        if (paginationScrollTargetRef?.current) {
            paginationScrollTargetRef.current.scrollIntoView();
        }
        updatePagination(pageNumber);
    };

    const seenProducts = currentPageWithLoadMore * pageSize;
    const remainingProducts = totalCount - seenProducts;
    const loadMoreCount = remainingProducts > pageSize ? pageSize : remainingProducts;

    return (
        <div className="flex flex-col items-center justify-between gap-5 vl:flex-row">
            {isWithLoadMore && hasNextPage && (
                <Button className="px-3" variant="inverted" onClick={loadMore}>
                    {type === 'blog' ? (
                        <>
                            {t('Load more')} {loadMoreCount} {t('articles count', { count: loadMoreCount })}
                        </>
                    ) : (
                        <>
                            {t('Load more')} {loadMoreCount} {t('products count', { count: loadMoreCount })}
                        </>
                    )}
                </Button>
            )}

            <div className="ml-auto flex gap-1">
                {paginationButtons.map((pageNumber, index, array) => {
                    const urlPageNumber = pageNumber > 1 ? pageNumber.toString() : undefined;
                    const pageParams = urlPageNumber
                        ? new URLSearchParams({ ...queryParams, page: urlPageNumber }).toString()
                        : undefined;
                    const pageHref = `${asPathWithoutQueryParams}${pageParams ? `?${pageParams}` : ''}`;

                    return (
                        <Fragment key={pageNumber}>
                            {isDotKey(array[index - 1] ?? null, pageNumber) && (
                                <PaginationButton isDotButton>&#8230;</PaginationButton>
                            )}
                            {currentPageWithLoadMore === pageNumber ? (
                                <PaginationButton isActive>{pageNumber}</PaginationButton>
                            ) : (
                                <PaginationButton href={pageHref} onClick={onChangePage(pageNumber)}>
                                    {pageNumber}
                                </PaginationButton>
                            )}
                        </Fragment>
                    );
                })}
            </div>
        </div>
    );
};

const isDotKey = (prevPage: number | null, currentPage: number): boolean => {
    return prevPage !== null && prevPage !== currentPage - 1;
};

type PaginationButtonProps = {
    isActive?: boolean;
    isDotButton?: boolean;
    href?: string;
    onClick?: () => void;
};

const PaginationButton: FC<PaginationButtonProps> = forwardRef(
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    ({ children, isActive, isDotButton, href, onClick }, _) => {
        const handleOnClick: MouseEventHandler<HTMLAnchorElement> = (e) => {
            e.preventDefault();

            if (onClick) {
                onClick();
            }
        };

        const Tag = isActive ? 'span' : 'a';

        return (
            <Tag
                href={href}
                className={twJoin(
                    'flex size-8 items-center justify-center rounded-lg border-2 font-bold no-underline hover:no-underline md:size-12',
                    (isActive || isDotButton) && 'border-none hover:cursor-default',
                    isActive
                        ? 'border-actionInvertedBorderActive bg-actionInvertedBackgroundActive text-actionInvertedTextActive'
                        : 'border-actionInvertedBorder bg-actionInvertedBackground text-actionInvertedText hover:border-actionInvertedBorderHovered hover:bg-actionInvertedBackgroundHovered hover:text-actionInvertedTextHovered',
                )}
                onClick={handleOnClick}
            >
                {children}
            </Tag>
        );
    },
);

PaginationButton.displayName = 'PaginationButton';
