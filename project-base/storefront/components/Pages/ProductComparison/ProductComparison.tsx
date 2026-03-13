import { ProductComparisonContent } from './ProductComparisonContent';
import { CompareIcon } from 'components/Basic/Icon/CompareIcon';
import { TrashCanIcon } from 'components/Basic/Icon/TrashCanIcon';
import { RemoveAllProductsPopup } from 'components/Blocks/Popup/RemoveAllProductsPopup';
import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { SkeletonModuleComparison } from 'components/Blocks/Skeleton/SkeletonModuleComparison';
import { Button } from 'components/Forms/Button/Button';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useGtmSliderProductListViewEvent } from 'gtm/utils/pageViewEvents/productList/useGtmSliderProductListViewEvent';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useComparison } from 'utils/productLists/comparison/useComparison';

export const ProductComparison: FC = () => {
    const { t } = useTranslation();
    const { comparison, isProductListFetching, removeComparison } = useComparison();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const title = `${t('Product comparison')}${comparison?.products.length ? ` (${comparison.products.length})` : ''}`;

    const handleRemoveAllClick = () => {
        updatePortalContent(
            <RemoveAllProductsPopup
                removeAllHandler={removeComparison}
                title={t('Do you really want to remove all products from comparison?')}
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
                                variant="inverted"
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

            <LastVisitedProducts />
        </VerticalStack>
    );
};
