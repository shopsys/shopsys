import { CompareIcon } from 'components/Basic/Icon/CompareIcon';
import { TrashCanIcon } from 'components/Basic/Icon/TrashCanIcon';
import { SkeletonModuleComparison } from 'components/Blocks/Skeleton/SkeletonModuleComparison';
import { Button } from 'components/Forms/Button/Button';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useComparisonPage } from 'utils/productLists/comparison/useComparisonPage';
import { ProductComparisonContent } from './ProductComparisonContent';

export const ProductComparison: FC = () => {
    const { t } = useTranslation();
    const { products, isLoading, emptyStateRef, handleRemove, handleRemoveAll } = useComparisonPage();
    const title = `${t('Product comparison')}${products.length ? ` (${products.length})` : ''}`;

    return (
        <VerticalStack gap="md">
            <Webline>
                {(products.length > 0 || isLoading) && (
                    <div className="mb-6 flex flex-wrap items-center justify-between gap-x-5">
                        <div>
                            <h1 data-tid={TIDs.page_title} tabIndex={-1}>
                                {title}
                            </h1>
                            {products.length === 1 && (
                                <p className="mt-2 text-sm text-text-less">
                                    {t('One more product and you can compare')}
                                </p>
                            )}
                        </div>

                        {!!products.length && (
                            <Button
                                aria-label={t('Remove all products from comparison', { ns: 'accessibility' })}
                                data-tid={TIDs.comparison_remove_all_button}
                                variant="tertiary"
                                onClick={handleRemoveAll}
                            >
                                <TrashCanIcon className="size-4" />
                                {t('Remove all from comparison')}
                            </Button>
                        )}
                    </div>
                )}
                {isLoading ? (
                    <SkeletonModuleComparison />
                ) : products.length ? (
                    <ProductComparisonContent comparedProducts={products} onRemove={handleRemove} />
                ) : (
                    <div data-tid={TIDs.comparison_empty_state} ref={emptyStateRef} tabIndex={-1}>
                        <PageHero
                            actionHref="/"
                            actionSkeletonType="homepage"
                            actionTitle={t('Discover our products')}
                            icon={CompareIcon}
                            title={t('Comparison')}
                            titleTid={TIDs.page_title}
                            description={t(
                                'Add products to compare their prices, availability and parameters side by side.',
                            )}
                        />
                    </div>
                )}
            </Webline>
        </VerticalStack>
    );
};
