import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { ReactNode, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useScrollTop } from 'utils/ui/useScrollTop';
import { PRODUCT_COMPARISON_END_TRIGGER_ID, PRODUCT_COMPARISON_STICKY_TRIGGER_ID } from './ProductComparisonHead';

type ProductComparisonHeadStickyProps = {
    comparedProducts: TypeProductInProductListFragment[];
    tableFirstColumnWidth: number | undefined;
    productColumnWidth?: number;
    tableMarginLeft: number;
    navigation?: ReactNode;
    viewportLeft?: number;
    viewportWidth?: number;
    onProductFocus?: (index: number) => void;
};

export const ProductComparisonHeadSticky: FC<ProductComparisonHeadStickyProps> = ({
    comparedProducts,
    tableFirstColumnWidth,
    productColumnWidth,
    tableMarginLeft,
    navigation,
    viewportLeft,
    viewportWidth,
    onProductFocus,
}) => {
    const { t } = useTranslation();
    const [hasPassedComparisonName, setHasPassedComparisonName] = useState(false);
    const [hasPassedComparisonTable, setHasPassedComparisonTable] = useState(false);
    useScrollTop(PRODUCT_COMPARISON_STICKY_TRIGGER_ID, setHasPassedComparisonName);
    useScrollTop(PRODUCT_COMPARISON_END_TRIGGER_ID, setHasPassedComparisonTable);
    const active = hasPassedComparisonName && !hasPassedComparisonTable;

    return (
        <div
            aria-hidden={!active}
            inert={!active}
            className={twJoin(
                'fixed top-(--sticky-navigation-offset,0px) left-0 z-menu flex w-full border-border-less border-b bg-table-bg-default shadow-sm transition-[transform,opacity,visibility] duration-300 motion-reduce:transition-none',
                active
                    ? 'visible translate-y-0 opacity-100'
                    : 'pointer-events-none invisible -translate-y-full opacity-0',
            )}
        >
            <div
                className="box-content flex min-w-0 shrink-0 border-border-less border-x"
                style={{ marginLeft: viewportLeft === undefined ? undefined : viewportLeft - 1, width: viewportWidth }}
            >
                <div className="hidden shrink-0 items-center px-5 md:flex" style={{ width: tableFirstColumnWidth }}>
                    {navigation ?? <span className="font-semibold text-sm">{t('Product comparison')}</span>}
                </div>
                <div className="min-w-0 flex-1 overflow-clip">
                    <div
                        className="grid grid-cols-2 md:flex"
                        style={{ transform: `translateX(-${tableMarginLeft}px)` }}
                    >
                        {comparedProducts.map((product, index) => (
                            <div
                                key={product.uuid}
                                className="min-w-0 shrink-0 border-border-less border-l p-3 max-md:first:border-l-0 md:w-(--product-column-width) md:px-5"
                                style={{ '--product-column-width': `${productColumnWidth}px` } as React.CSSProperties}
                            >
                                <ExtendedNextLink
                                    className="flex items-center gap-2 text-text-default no-underline hover:underline"
                                    onFocus={() => onProductFocus?.(index)}
                                    href={product.slug}
                                    type="product"
                                    aria-label={t('Go to product page of {{ productName }}', {
                                        ns: 'accessibility',
                                        productName: product.fullName,
                                    })}
                                >
                                    <Image
                                        alt=""
                                        className="hidden size-10 shrink-0 object-contain sm:block"
                                        height={40}
                                        src={product.mainImage?.url}
                                        width={40}
                                    />
                                    <span className="line-clamp-2 font-semibold text-xs">{product.fullName}</span>
                                </ExtendedNextLink>
                                <ProductPrice
                                    className="mt-1"
                                    isPriceFromVisible
                                    productPrice={product.price}
                                    textPriceSize="base"
                                />
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
};
