import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { Image } from 'components/Basic/Image/Image';
import { IconButton } from 'components/Forms/Button/IconButton';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { Select } from 'components/Forms/Select/Select';
import { m } from 'framer-motion';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { CSSProperties, useState } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getComparisonParameters } from 'utils/productLists/comparison/getComparisonParameters';
import { useComparisonFocus } from 'utils/productLists/comparison/useComparisonFocus';
import { useComparisonProducts } from 'utils/productLists/comparison/useComparisonProducts';
import { useComparisonTable } from 'utils/productLists/comparison/useComparisonTable';
import { ProductComparisonBody } from './ProductComparisonBody';
import { PRODUCT_COMPARISON_END_TRIGGER_ID, ProductComparisonHead } from './ProductComparisonHead';
import { ProductComparisonHeadSticky } from './ProductComparisonHeadSticky';

type ProductComparisonContentProps = {
    comparedProducts: TypeProductInProductListFragment[];
    onSaveOrder: (uuids: string[]) => Promise<boolean>;
    onRemove: (product: TypeProductInProductListFragment) => void;
};

export const ProductComparisonContent: FC<ProductComparisonContentProps> = ({
    comparedProducts,
    onRemove,
    onSaveOrder,
}) => {
    const { t } = useTranslation();
    const [onlyDifferences, setOnlyDifferences] = useState(false);
    const {
        mobileProducts,
        visibleProducts,
        selectMobileProduct,
        reorderProducts,
        canReorder,
        saveProductOrder,
        parameterSourceProducts,
    } = useComparisonProducts(comparedProducts, onSaveOrder);
    const { contentRef, handleFocusCapture } = useComparisonFocus(visibleProducts);
    const parameters = getComparisonParameters(visibleProducts, parameterSourceProducts);
    const table = useComparisonTable(visibleProducts.length);
    const hasMultipleProducts = visibleProducts.length > 1;
    const options = comparedProducts.map((product) => ({ value: product.uuid, label: product.fullName }));
    const differentParameterCount = parameters.filter((parameter) => parameter.isDifferent).length;

    const getMobileProductOptions = (productUuid: string) =>
        options.filter(
            (option) =>
                option.value === productUuid || !mobileProducts.some((product) => product.uuid === option.value),
        );

    const navigation = table.shouldShowArrows ? (
        <div className="flex items-center gap-1">
            <IconButton
                Icon={ArrowSecondaryIcon}
                disabled={!table.isArrowLeftActive}
                iconClassName="rotate-90"
                shape="rounded"
                ariaLabel={t('Show previous product in comparison', { ns: 'accessibility' })}
                title={t('Previous product')}
                variant="ghost"
                onClick={table.handleSlideLeft}
            />
            <IconButton
                Icon={ArrowSecondaryIcon}
                disabled={!table.isArrowRightActive}
                iconClassName="-rotate-90"
                shape="rounded"
                ariaLabel={t('Show next product in comparison', { ns: 'accessibility' })}
                title={t('Next product')}
                variant="ghost"
                onClick={table.handleSlideRight}
            />
        </div>
    ) : undefined;

    return (
        <section
            aria-label={t('Product comparison')}
            ref={contentRef}
            onFocusCapture={handleFocusCapture}
            className="relative mx-auto w-full"
            style={{ maxWidth: 192 + comparedProducts.length * 360 }}
        >
            {comparedProducts.length > 2 && (
                <div className="mb-4 grid grid-cols-2 gap-3 md:hidden">
                    {mobileProducts.map((product, index) => (
                        <Select
                            key={index}
                            activeOption={options.find((option) => option.value === product.uuid)}
                            ariaLabel={t('Select product {{ number }}', { number: index + 1 })}
                            label={t('Product {{ number }}', { number: index + 1 })}
                            options={getMobileProductOptions(product.uuid)}
                            selectClassName="rounded-b-md"
                            listClassName={`mt-2 w-[calc(200%+0.75rem)] max-h-64 rounded-md border-t-2 [&_[role=option]>div]:grid [&_[role=option]>div]:grid-cols-[minmax(0,1fr)_1rem] [&_[role=option]>div]:gap-3 [&_[role=option]>div]:py-4 ${index === 0 ? 'right-auto' : 'left-auto'}`}
                            renderOption={(option) => (
                                <div className="flex min-w-0 items-center gap-3">
                                    <Image
                                        alt=""
                                        className="size-10 shrink-0 object-contain mix-blend-multiply"
                                        height={40}
                                        src={
                                            comparedProducts.find((item) => item.uuid === option.value)?.mainImage?.url
                                        }
                                        width={40}
                                    />
                                    <span className="wrap-break-word min-w-0 font-semibold leading-relaxed">
                                        {option.label}
                                    </span>
                                </div>
                            )}
                            onSelectOption={(option) => selectMobileProduct(index, option.value)}
                        />
                    ))}
                </div>
            )}
            <div id={PRODUCT_COMPARISON_END_TRIGGER_ID}>
                <ProductComparisonHeadSticky
                    key={visibleProducts[0]?.uuid}
                    comparedProducts={visibleProducts}
                    navigation={navigation}
                    onProductFocus={table.revealProduct}
                    viewportLeft={table.viewportLeft}
                    viewportWidth={table.viewportWidth}
                    productColumnWidth={table.productColumnWidth}
                    tableFirstColumnWidth={table.tableFirstColumnWidth}
                    tableMarginLeft={table.tableMarginLeft}
                />
                <m.section
                    layoutScroll
                    aria-label={t('Compared products and parameters')}
                    className="relative overflow-x-auto overscroll-x-contain rounded-lg border border-border-less"
                    ref={table.scrollRef}
                    onScroll={table.calcMaxMarginLeft}
                >
                    <table
                        className="block w-full table-fixed border-collapse md:table md:min-w-(--comparison-min-width)"
                        style={
                            {
                                '--comparison-min-width': `${192 + visibleProducts.length * 240}px`,
                                '--comparison-viewport-width': `${table.viewportWidth}px`,
                            } as CSSProperties
                        }
                    >
                        <caption className="sr-only">{t('Product comparison')}</caption>
                        <colgroup>
                            <col className="w-48" />
                            {visibleProducts.map((product) => (
                                <col key={product.uuid} />
                            ))}
                        </colgroup>
                        <ProductComparisonHead
                            allProducts={comparedProducts}
                            comparedProducts={visibleProducts}
                            onRemove={onRemove}
                            onReorder={reorderProducts}
                            onReorderEnd={saveProductOrder}
                            canReorder={canReorder}
                            onProductFocus={table.revealProduct}
                        />
                        {hasMultipleProducts && (
                            <tbody className="block md:table-row-group">
                                <tr className="block md:table-row">
                                    <td
                                        className="block border-border-less border-t bg-table-bg-default p-0 md:table-cell"
                                        colSpan={visibleProducts.length + 1}
                                    >
                                        <div
                                            className="flex flex-wrap items-center justify-between gap-4 p-4 md:sticky md:left-0"
                                            style={{ width: table.viewportWidth || undefined }}
                                        >
                                            <div className="shrink-0">
                                                <Checkbox
                                                    id="comparison-only-differences"
                                                    label={
                                                        <>
                                                            {t('Show only differences')}{' '}
                                                            <span aria-live="polite">({differentParameterCount})</span>
                                                        </>
                                                    }
                                                    value={onlyDifferences}
                                                    onChange={(event) => setOnlyDifferences(event.target.checked)}
                                                />
                                            </div>
                                            {navigation}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        )}
                        <ProductComparisonBody
                            onlyDifferences={onlyDifferences && hasMultipleProducts}
                            parameters={parameters}
                            productCount={visibleProducts.length}
                        />
                    </table>
                </m.section>
            </div>
        </section>
    );
};
