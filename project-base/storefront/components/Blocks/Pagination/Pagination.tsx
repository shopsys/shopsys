import { Button } from 'components/Forms/Button/Button';
import { usePaginationContext } from 'components/providers/PaginationProvider';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { useRouter } from 'next/router';
import { Fragment, forwardRef, MouseEventHandler } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getUrlQueriesWithoutDynamicPageQueries } from 'utils/parsing/getUrlQueriesWithoutDynamicPageQueries';
import { useCurrentLoadMoreQuery } from 'utils/queryParams/useCurrentLoadMoreQuery';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';
import { useUpdateLoadMoreQuery } from 'utils/queryParams/useUpdateLoadMoreQuery';
import { useUpdatePaginationQuery } from 'utils/queryParams/useUpdatePaginationQuery';
import { twMergeCustom } from 'utils/twMerge';
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
            paginationScrollTargetRef?.current?.scrollIntoView({ behavior: 'smooth' });
        }, 100);
    };

    const seenProducts = currentPageWithLoadMore * pageSize;
    const remainingProducts = totalCount - seenProducts;
    const loadMoreCount = remainingProducts > pageSize ? pageSize : remainingProducts;
    const itemsLabel =
        type === 'blog' ? t('articles count', { count: loadMoreCount }) : t('products count', { count: loadMoreCount });

    return (
        <div className="mt-5 flex vl:flex-row flex-col items-center justify-between gap-5">
            {isWithLoadMore && hasNextPage && loadMoreCount > 0 && (
                <Button variant="secondary" onClick={loadMore}>
                    {t('Load {{ count }} more {{ items }}', { count: loadMoreCount, items: itemsLabel })}
                </Button>
            )}

            <nav aria-label={t('Pagination navigation', { ns: 'accessibility' })} className="ml-auto">
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
                aria-label={!isActive ? t('Go to page {{ page }}', { ns: 'accessibility', page: children }) : undefined}
                href={href}
                tabIndex={isActive ? -1 : 0}
                className={twMergeCustom(
                    'flex size-8 items-center justify-center rounded-lg border-2 font-bold no-underline hover:no-underline md:size-12',
                    (isActive || isDotButton) && 'border-none hover:cursor-default',
                    isActive
                        ? 'border-button-secondary-border-active bg-button-secondary-bg-active text-button-secondary-text-active'
                        : 'border-button-secondary-border-default bg-button-secondary-bg-default text-button-secondary-text-default hover:border-button-secondary-border-hovered hover:bg-button-secondary-bg-hovered hover:text-button-secondary-text-hovered',
                )}
                onClick={handleOnClick}
            >
                {children}
            </Tag>
        );
    },
);

PaginationButton.displayName = 'PaginationButton';
