import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { generateProductImageAlt } from 'utils/productAltText';
import { useScrollTop } from 'utils/ui/useScrollTop';

type ProductComparisonHeadStickyProps = {
    comparedProducts: TypeProductInProductListFragment[];
    tableMarginLeft: number;
};

export const ProductComparisonHeadSticky: FC<ProductComparisonHeadStickyProps> = (props) => {
    return (
        <ProductComparisonHeadStickyWrapper>
            <ProductComparisonHeadStickyContent
                comparedProducts={props.comparedProducts}
                tableMarginLeft={props.tableMarginLeft}
            />
        </ProductComparisonHeadStickyWrapper>
    );
};

const ProductComparisonHeadStickyWrapper = ({ children }: { children: React.ReactNode }) => {
    const [tableStickyHeadActive, setTableStickyHeadActive] = useState(false);
    useScrollTop('js-table-compare', setTableStickyHeadActive);

    return (
        <div
            className={twJoin(
                'fixed top-0 left-0 z-menu w-full overflow-hidden border-border-less border-b-2 bg-table-bg-contrast px-5',
                tableStickyHeadActive ? 'flex' : 'hidden',
            )}
        >
            {children}
        </div>
    );
};

type ProductComparisonContentProps = {
    comparedProducts: TypeProductInProductListFragment[];
    tableMarginLeft: number;
};

const ProductComparisonHeadStickyContent = ({ comparedProducts, tableMarginLeft }: ProductComparisonContentProps) => (
    <div className="mx-auto flex w-full max-w-7xl flex-nowrap overflow-hidden">
        <div className="static flex h-full min-w-[115px] vl:min-w-[290px] max-w-[182px] shrink-0 border-r-1 sm:w-auto sm:min-w-[220px] sm:max-w-none md:min-w-[265px] md:max-w-none lg:min-w-[270px]" />
        {comparedProducts.map((product, index) => (
            <div
                key={`headSticky-${product.uuid}`}
                className="flex min-w-[calc(182px+12px*2)] max-w-[calc(182px+12px*2)] shrink-0 basis-64 items-center border-r-1 px-1 py-3 sm:min-w-[calc(205px+20px*2)] sm:max-w-[calc(205px+20px*2)]"
                style={index === 0 ? { marginLeft: -tableMarginLeft } : undefined}
            >
                <ExtendedNextLink className="relative size-16" href={product.slug} tabIndex={0}>
                    <Image
                        fill
                        alt={generateProductImageAlt(product.fullName, product.categories[0]?.name)}
                        className="object-contain"
                        src={product.mainImage?.url}
                    />
                </ExtendedNextLink>
                <div className="ml-2 flex flex-1 flex-col">
                    <ExtendedNextLink
                        className="text-xs no-underline hover:no-underline"
                        href={product.slug}
                        tabIndex={0}
                    >
                        {product.fullName}
                    </ExtendedNextLink>
                </div>
            </div>
        ))}
    </div>
);
