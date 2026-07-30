import { CompareIcon } from 'components/Basic/Icon/CompareIcon';
import { TrashCanIcon } from 'components/Basic/Icon/TrashCanIcon';
import { DeferredLastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts';
import { SkeletonModuleComparison } from 'components/Blocks/Skeleton/SkeletonModuleComparison';
import { Button } from 'components/Forms/Button/Button';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useGtmSliderProductListViewEvent } from 'gtm/utils/pageReadyEvents/productList/useGtmSliderProductListViewEvent';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { ProductComparisonContent } from './ProductComparisonContent';

const RemoveAllProductsPopup = dynamic(
    () =>
        import('components/Blocks/Popup/RemoveAllProductsPopup').then((component) => component.RemoveAllProductsPopup),
    {
        ssr: false,
    },
);

export const ProductComparison: FC = () => {
    const { t } = useTranslation();
    const { comparison, isProductListFetching, removeComparison } = useComparison();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const title = `${t('Product comparison')}${comparison?.products.length ? ` (${comparison.products.length})` : ''}`;

    const handleRemoveAllClick = () => {
        updatePortalContent(
            <RemoveAllProductsPopup
                description={t('All products in comparison will be removed ({{ productCount }} in total).', {
                    productCount: comparison?.products.length ?? 0,
                })}
                removeAllHandler={removeComparison}
            />,
        );
    };

    useGtmSliderProductListViewEvent(comparison?.products, GtmProductListNameType.product_comparison_page);

    return (
        <VerticalStack gap="md">
            <Webline>
                {isProductListFetching && <SkeletonModuleComparison />}

                {comparison?.products && !isProductListFetching && (
                    <>
                        <div className="mb-4 flex flex-wrap items-center justify-between gap-4">
                            <h1 data-tid={TIDs.page_title}>{title}</h1>

                            <Button
                                aria-label={t('Remove all products from comparison', { ns: 'accessibility' })}
                                data-tid={TIDs.comparison_remove_all_button}
                                variant="tertiary"
                                onClick={handleRemoveAllClick}
                            >
                                <TrashCanIcon className="size-4" />
                                {t('Remove all from comparison')}
                            </Button>
                        </div>

                        <ProductComparisonContent comparedProducts={comparison.products} />
                    </>
                )}

                {!comparison?.products && !isProductListFetching && (
                    <div data-tid={TIDs.comparison_empty_state}>
                        <PageHero
                            actionHref="/"
                            actionSkeletonType="homepage"
                            actionTitle={t('Find products')}
                            icon={CompareIcon}
                            title={t('Comparison')}
                            description={t(
                                'Your comparison list is empty! Start comparing products to see their features side by side.',
                            )}
                        />
                    </div>
                )}
            </Webline>

            <DeferredLastVisitedProducts />
        </VerticalStack>
    );
};
