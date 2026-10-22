import { HeartIcon } from 'components/Basic/Icon/HeartIcon';
import { TrashCanIcon } from 'components/Basic/Icon/TrashCanIcon';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { DeferredLastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts';
import { ProductListViewModeToggle } from 'components/Blocks/Product/ProductsList/ProductListViewModeToggle';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { SkeletonModuleWishlist } from 'components/Blocks/Skeleton/SkeletonModuleWishlist';
import { Button } from 'components/Forms/Button/Button';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { PaginationProvider } from 'components/providers/PaginationProvider';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { useEffect, useRef } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { calculatePageSize } from 'utils/loadMore/calculatePageSize';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';
import { LOAD_MORE_QUERY_PARAMETER_NAME, PAGE_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';
import { pushQueries } from 'utils/queryParams/pushQueries';
import { useCurrentLoadMoreQuery } from 'utils/queryParams/useCurrentLoadMoreQuery';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';

const RemoveAllProductsPopup = dynamic(
    () =>
        import('components/Blocks/Popup/RemoveAllProductsPopup').then((component) => component.RemoveAllProductsPopup),
    {
        ssr: false,
    },
);

export const Wishlist: FC = () => {
    const { t } = useTranslation();
    const { wishlist, isProductListFetching, removeWishlist } = useWishlist();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const currentPage = useCurrentPageQuery();
    const currentLoadMore = useCurrentLoadMoreQuery();
    const router = useRouter();
    const totalCount = wishlist?.products.length ?? 0;
    const lastPage = Math.max(1, Math.ceil(totalCount / DEFAULT_PAGE_SIZE));
    const page = Number.isInteger(currentPage) && currentPage > 0 ? Math.min(currentPage, lastPage) : 1;
    const loadMore = Number.isInteger(currentLoadMore) && currentLoadMore >= 0 ? currentLoadMore : 0;
    const offset = (page - 1) * DEFAULT_PAGE_SIZE;
    const visibleProducts = wishlist?.products.slice(offset, offset + calculatePageSize(loadMore));

    useEffect(() => {
        if (!isProductListFetching && (currentPage !== page || currentLoadMore !== loadMore)) {
            pushQueries(router, {
                ...router.query,
                [PAGE_QUERY_PARAMETER_NAME]: page > 1 ? page.toString() : undefined,
                [LOAD_MORE_QUERY_PARAMETER_NAME]: undefined,
            });
        }
    }, [isProductListFetching, currentPage, page, currentLoadMore, loadMore, router]);

    const handleRemoveAllClick = () => {
        updatePortalContent(
            <RemoveAllProductsPopup
                description={t('All products in wishlist will be removed ({{ productCount }} in total).', {
                    productCount: wishlist?.products.length ?? 0,
                })}
                removeAllHandler={removeWishlist}
            />,
        );
    };

    return (
        <VerticalStack gap="md">
            <Webline>
                {isProductListFetching && <SkeletonModuleWishlist />}

                {totalCount > 0 && !isProductListFetching && (
                    <div className="scroll-mt-fixed-header" ref={paginationScrollTargetRef}>
                        <div className="mb-4 flex flex-col gap-2">
                            <div className="flex items-center justify-between gap-2">
                                <h1>{t('Wishlist')}</h1>

                                <Button
                                    aria-label={t('Remove all product from wishlist', { ns: 'accessibility' })}
                                    size="small"
                                    title={t('Remove all from wishlist')}
                                    variant="tertiary"
                                    onClick={handleRemoveAllClick}
                                >
                                    <TrashCanIcon className="size-4" />
                                    {t('Remove all')}
                                </Button>
                            </div>

                            <div className="flex items-center justify-between gap-2 sm:justify-end">
                                <div className="font-secondary text-input-placeholder-default text-xs">
                                    {totalCount} {t('products count', { count: totalCount })}
                                </div>

                                <ProductListViewModeToggle />
                            </div>
                        </div>

                        <ProductsList
                            areProductsFetching={isProductListFetching}
                            gtmMessageOrigin={GtmMessageOriginType.other}
                            gtmProductListName={GtmProductListNameType.wishlist}
                            products={visibleProducts}
                        />

                        <PaginationProvider paginationScrollTargetRef={paginationScrollTargetRef}>
                            <Pagination
                                isWithLoadMore
                                hasNextPage={offset + (visibleProducts?.length ?? 0) < totalCount}
                                totalCount={totalCount}
                            />
                        </PaginationProvider>
                    </div>
                )}

                {totalCount === 0 && !isProductListFetching && (
                    <PageHero
                        actionHref="/"
                        actionSkeletonType="homepage"
                        actionTitle={t('Discover our products')}
                        icon={HeartIcon}
                        title={t('Wishlist')}
                        description={t(
                            'Your wishlist is feeling empty! Add products you love so you can easily find them here later.',
                        )}
                    />
                )}
            </Webline>

            <DeferredLastVisitedProducts />
        </VerticalStack>
    );
};
