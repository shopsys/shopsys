import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useScrollTop } from 'utils/ui/useScrollTop';
import { PRODUCT_COMPARISON_END_TRIGGER_ID, PRODUCT_COMPARISON_STICKY_TRIGGER_ID } from './ProductComparisonHead';

type ProductComparisonHeadStickyProps = {
    comparedProducts: TypeProductInProductListFragment[];
    tableFirstColumnWidth: number | undefined;
    tableMarginLeft: number;
};

export const ProductComparisonHeadSticky: FC<ProductComparisonHeadStickyProps> = (props) => {
    return (
        <ProductComparisonHeadStickyWrapper>
            <ProductComparisonHeadStickyContent
                comparedProducts={props.comparedProducts}
                tableFirstColumnWidth={props.tableFirstColumnWidth}
                tableMarginLeft={props.tableMarginLeft}
            />
        </ProductComparisonHeadStickyWrapper>
    );
};

const ProductComparisonHeadStickyWrapper = ({ children }: { children: React.ReactNode }) => {
    const [hasPassedComparisonName, setHasPassedComparisonName] = useState(false);
    const [hasPassedComparisonTable, setHasPassedComparisonTable] = useState(false);
    useScrollTop(PRODUCT_COMPARISON_STICKY_TRIGGER_ID, setHasPassedComparisonName);
    useScrollTop(PRODUCT_COMPARISON_END_TRIGGER_ID, setHasPassedComparisonTable);

    const isTableStickyHeadActive = hasPassedComparisonName && !hasPassedComparisonTable;

    return (
        <div
            aria-hidden={!isTableStickyHeadActive}
            className={twJoin(
                'fixed top-(--sticky-navigation-offset,0px) left-0 z-menu flex w-full overflow-hidden border-border-less border-b-2 bg-table-bg-contrast transition-[transform,opacity,visibility] duration-300 ease-out motion-reduce:transition-none',
                isTableStickyHeadActive
                    ? 'visible translate-y-0 opacity-100'
                    : 'pointer-events-none invisible -translate-y-full opacity-0',
            )}
        >
            {children}
        </div>
    );
};

type ProductComparisonContentProps = {
    comparedProducts: TypeProductInProductListFragment[];
    tableFirstColumnWidth: number | undefined;
    tableMarginLeft: number;
};

const ProductComparisonHeadStickyContent = ({
    comparedProducts,
    tableFirstColumnWidth,
    tableMarginLeft,
}: ProductComparisonContentProps) => {
    const { t } = useTranslation();

    return (
        <Webline className="flex flex-nowrap overflow-hidden">
            <div
                className="static flex h-full w-28.75 min-w-28.75 max-w-28.75 shrink-0 border-r"
                style={
                    tableFirstColumnWidth === undefined
                        ? undefined
                        : {
                              width: tableFirstColumnWidth,
                              minWidth: tableFirstColumnWidth,
                              maxWidth: tableFirstColumnWidth,
                          }
                }
            />
            {comparedProducts.map((product, index) => (
                <div
                    key={`headSticky-${product.uuid}`}
                    className="flex min-w-[calc(182px+12px*2)] max-w-[calc(182px+12px*2)] shrink-0 basis-64 items-center border-r px-1 py-3 sm:min-w-[calc(205px+20px*2)] sm:max-w-[calc(205px+20px*2)]"
                    style={index === 0 ? { marginLeft: -tableMarginLeft } : undefined}
                >
                    <ExtendedNextLink
                        className="group/product-link flex flex-1 items-center text-text-default no-underline hover:text-text-default hover:no-underline focus-visible:outline-hidden"
                        data-focus-color="preserve"
                        draggable={false}
                        href={product.slug}
                        type="product"
                        aria-label={t('Go to product page of {{ productName }}', {
                            ns: 'accessibility',
                            productName: product.fullName,
                        })}
                    >
                        <div className="relative size-16 shrink-0">
                            <Image
                                alt=""
                                className="size-full object-contain mix-blend-multiply"
                                height={64}
                                src={product.mainImage?.url}
                                width={64}
                            />
                        </div>
                        <span className="ml-2 flex-1 rounded-sm text-link-default text-xs group-hover/product-link:text-link-hovered group-hover/product-link:underline group-focus-visible/product-link:bg-orange-500 group-focus-visible/product-link:text-text-default!">
                            {product.fullName}
                        </span>
                    </ExtendedNextLink>
                </div>
            ))}
        </Webline>
    );
};
