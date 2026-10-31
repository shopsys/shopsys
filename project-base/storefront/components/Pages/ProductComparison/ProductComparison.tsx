import { CompareIcon } from 'components/Basic/Icon/CompareIcon';
import { TrashCanIcon } from 'components/Basic/Icon/TrashCanIcon';
import { SkeletonModuleComparison } from 'components/Blocks/Skeleton/SkeletonModuleComparison';
import { Button } from 'components/Forms/Button/Button';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { useEffect, useRef } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useComparisonPage } from 'utils/productLists/comparison/useComparisonPage';
import { ProductComparisonContent } from './ProductComparisonContent';
import { ProductComparisonVariantGroup } from './ProductComparisonVariantGroup';

export const ProductComparison: FC = () => {
    const { t } = useTranslation();
    const { products, isLoading, emptyStateRef, handleRemove, handleRemoveAll, moveProduct } = useComparisonPage();
    const concreteProducts = products.filter((product) => product.__typename !== 'MainVariant');
    const variantGroups = products.filter((product) => product.__typename === 'MainVariant');
    const titleRef = useRef<HTMLHeadingElement>(null);
    const previousGroupUuids = useRef<string[]>([]);

    useEffect(() => {
        const removedGroup = previousGroupUuids.current.some(
            (uuid) => !products.some((product) => product.uuid === uuid),
        );
        previousGroupUuids.current = products
            .filter((product) => product.__typename === 'MainVariant')
            .map((product) => product.uuid);

        if (removedGroup && document.activeElement === document.body) {
            titleRef.current?.focus({ preventScroll: true });
        }
    }, [products]);
    const title = `${t('Product comparison')}${products.length ? ` (${products.length})` : ''}`;

    return (
        <VerticalStack gap="md">
            <Webline>
                {(products.length > 0 || isLoading) && (
                    <div className="mb-6 flex flex-wrap items-center justify-between gap-x-5">
                        <div>
                            <h1 data-tid={TIDs.page_title} ref={titleRef} tabIndex={-1}>
                                {title}
                            </h1>
                            {concreteProducts.length === 1 && (
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
                    <div className="flex flex-col gap-5">
                        {variantGroups.map((product) => (
                            <ProductComparisonVariantGroup
                                key={product.uuid}
                                product={product}
                                listIndex={products.findIndex((item) => item.uuid === product.uuid)}
                                onRemove={() =>
                                    handleRemove(
                                        product,
                                        products.findIndex((item) => item.uuid === product.uuid),
                                    )
                                }
                            />
                        ))}
                        {concreteProducts.length > 0 && (
                            <ProductComparisonContent
                                comparedProducts={concreteProducts}
                                onRemove={(product) =>
                                    handleRemove(
                                        product,
                                        products.findIndex((item) => item.uuid === product.uuid),
                                    )
                                }
                                onMoveProduct={moveProduct}
                            />
                        )}
                    </div>
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
