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
                'z-menu border-border-less bg-table-bg-contrast fixed top-0 left-0 w-full overflow-hidden border-b-2 px-5',
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
        <div className="vl:min-w-[290px] static flex h-full max-w-[182px] min-w-[115px] shrink-0 border-r-1 sm:w-auto sm:max-w-none sm:min-w-[220px] md:max-w-none md:min-w-[265px] lg:min-w-[270px]" />
        {comparedProducts.map((product, index) => (
            <div
                key={`headSticky-${product.uuid}`}
                className="flex max-w-[calc(182px+12px*2)] min-w-[calc(182px+12px*2)] shrink-0 basis-64 items-center border-r-1 px-1 py-3 sm:max-w-[calc(205px+20px*2)] sm:min-w-[calc(205px+20px*2)]"
                style={index === 0 ? { marginLeft: -tableMarginLeft } : undefined}
            >
                <a className="relative size-16" href={product.slug} tabIndex={0}>
                    <Image
                        fill
                        alt={generateProductImageAlt(product.fullName, product.categories[0]?.name)}
                        className="object-contain"
                        src={product.mainImage?.url}
                    />
                </a>
                <div className="ml-2 flex flex-1 flex-col">
                    <a className="text-xs no-underline hover:no-underline" href={product.slug} tabIndex={0}>
                        {product.fullName}
                    </a>
                </div>
            </div>
        ))}
    </div>
);
