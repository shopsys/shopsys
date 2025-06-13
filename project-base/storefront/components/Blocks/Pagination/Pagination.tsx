import { Button } from 'components/Forms/Button/Button';
import { usePaginationContext } from 'components/providers/PaginationProvider';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { Fragment, MouseEventHandler, forwardRef } from 'react';
import { twJoin } from 'tailwind-merge';
import { getUrlQueriesWithoutDynamicPageQueries } from 'utils/parsing/getUrlQueriesWithoutDynamicPageQueries';
import { useCurrentLoadMoreQuery } from 'utils/queryParams/useCurrentLoadMoreQuery';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';
import { useUpdateLoadMoreQuery } from 'utils/queryParams/useUpdateLoadMoreQuery';
import { useUpdatePaginationQuery } from 'utils/queryParams/useUpdatePaginationQuery';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { usePagination } from 'utils/ui/usePagination';
import { useScrollRestoration } from 'utils/ui/useScrollRestoration';

type PaginationProps = {
    totalCount: number;
    hasNextPage?: boolean;
    isWithLoadMore?: boolean;
    pageSize?: number;
    type?: 'default' | 'blog';
};

export const Pagination: FC<PaginationProps> = ({
    totalCount,
    hasNextPage,
    isWithLoadMore,
    pageSize = DEFAULT_PAGE_SIZE,
    type = 'default',
}) => {
    const { paginationScrollTargetRef } = usePaginationContext();
    const router = useRouter();
    const isDesktop = useMediaMin('sm');
    const currentPage = useCurrentPageQuery();
    const currentLoadMore = useCurrentLoadMoreQuery();
    const updatePagination = useUpdatePaginationQuery();
    const loadMore = useUpdateLoadMoreQuery();
    const currentPageWithLoadMore = Math.min(currentPage + currentLoadMore, Math.ceil(totalCount / pageSize));
    const paginationButtons = usePagination(totalCount, currentPageWithLoadMore, !isDesktop, pageSize);
    const { t } = useTranslation();

    useScrollRestoration({
        scrollTargetRef: paginationScrollTargetRef,
        shouldScroll: currentPage > 1,
    });

    if (!paginationButtons || paginationButtons.length === 1) {
        return null;
    }

    const asPathWithoutQueryParams = router.asPath.split('?')[0];
    const queryParams = getUrlQueriesWithoutDynamicPageQueries(router.query);

    const onChangePage = (pageNumber: number) => () => {
        updatePagination(pageNumber);
        // timeout for safari scroll
        setTimeout(() => {
            if (paginationScrollTargetRef?.current) {
                paginationScrollTargetRef.current.scrollIntoView({ behavior: 'smooth' });
            }
        }, 100);
    };

    const seenProducts = currentPageWithLoadMore * pageSize;
    const remainingProducts = totalCount - seenProducts;
    const loadMoreCount = remainingProducts > pageSize ? pageSize : remainingProducts;
    const itemsLabel =
        type === 'blog' ? t('articles count', { count: loadMoreCount }) : t('products count', { count: loadMoreCount });

    return (
        <div className="vl:flex-row mt-5 flex flex-col items-center justify-between gap-5">
            {isWithLoadMore && hasNextPage && loadMoreCount > 0 && (
                <Button variant="inverted" onClick={loadMore}>
                    {t('Load {{ count }} more {{ items }}', { count: loadMoreCount, items: itemsLabel })}
                </Button>
            )}

            <nav aria-label={t('Pagination navigation')} className="ml-auto">
                <div className="flex gap-1">
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
            </nav>
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
        const { t } = useTranslation();

        const handleOnClick: MouseEventHandler<HTMLAnchorElement> = (e) => {
            e.preventDefault();

            if (onClick) {
                onClick();
            }
        };

        const Tag = isActive ? 'span' : 'a';

        return (
            <Tag
                aria-current={isActive ? 'page' : undefined}
                aria-label={!isActive ? t('Go to page {{ page }}', { page: children }) : undefined}
                href={href}
                tabIndex={0}
                className={twJoin(
                    'flex size-8 items-center justify-center rounded-lg border-2 font-bold no-underline hover:no-underline md:size-12',
                    (isActive || isDotButton) && 'border-none hover:cursor-default',
                    isActive
                        ? 'border-button-inverted-border-active bg-button-inverted-bg-active text-button-inverted-text-active'
                        : 'border-button-inverted-border-default bg-button-inverted-bg-default text-button-inverted-text-default hover:border-button-inverted-border-hovered hover:bg-button-inverted-bg-hovered hover:text-button-inverted-text-hovered',
                )}
                onClick={handleOnClick}
            >
                {children}
            </Tag>
        );
    },
);

PaginationButton.displayName = 'PaginationButton';
