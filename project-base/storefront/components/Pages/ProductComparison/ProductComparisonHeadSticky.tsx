import { Image } from 'components/Basic/Image/Image';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { twJoin } from 'tailwind-merge';
import { useComparisonTable } from 'utils/productLists/comparison/useComparisonTable';

type ProductComparisonHeadStickyProps = {
    comparedProducts: TypeProductInProductListFragment[];
    tableMarginLeft: number;
};

export const ProductComparisonHeadSticky: FC<ProductComparisonHeadStickyProps> = (props) => {
    const { tableStickyHeadActive } = useComparisonTable(props.comparedProducts.length);

    return (
        <div
            className={twJoin(
                'z-menu border-borderAccentLess bg-tableBackgroundContrast fixed top-0 left-0 w-full overflow-hidden border-b-2 px-5',
                tableStickyHeadActive ? 'flex' : 'hidden',
            )}
        >
            <div className="mx-auto flex w-full max-w-7xl flex-nowrap overflow-hidden">
                <div className="vl:min-w-[290px] static flex h-full max-w-[182px] min-w-[115px] shrink-0 border-r-1 sm:w-auto sm:max-w-none sm:min-w-[220px] md:max-w-none md:min-w-[265px] lg:min-w-[270px]" />
                {props.comparedProducts.map((product, index) => (
                    <div
                        key={`headSticky-${product.uuid}`}
                        className="flex max-w-[calc(182px+12px*2)] min-w-[calc(182px+12px*2)] shrink-0 basis-64 items-center border-r-1 px-1 py-3 sm:max-w-[calc(205px+20px*2)] sm:min-w-[calc(205px+20px*2)]"
                        style={index === 0 ? { marginLeft: -props.tableMarginLeft } : undefined}
                    >
                        <a className="relative h-16 w-16" href={product.slug}>
                            <Image
                                fill
                                alt={product.mainImage?.name || product.fullName}
                                className="object-contain"
                                src={product.mainImage?.url}
                            />
                        </a>
                        <div className="ml-2 flex flex-1 flex-col">
                            <a className="text-xs no-underline hover:no-underline" href={product.slug}>
                                {product.fullName}
                            </a>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
};
