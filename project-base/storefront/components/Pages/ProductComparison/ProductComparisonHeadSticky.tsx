import { Image } from 'components/Basic/Image/Image';
import { ProductInProductListFragmentApi } from 'graphql/generated';
import { useComparisonTable } from 'hooks/productLists/comparison/useComparisonTable';
import { twJoin } from 'tailwind-merge';

type ProductComparisonHeadStickyProps = {
    comparedProducts: ProductInProductListFragmentApi[];
    tableMarginLeft: number;
};

export const ProductComparisonHeadSticky: FC<ProductComparisonHeadStickyProps> = (props) => {
    const { tableStickyHeadActive } = useComparisonTable(props.comparedProducts.length);

    return (
        <div
            className={twJoin(
                'fixed top-0 left-0 z-[2] w-full overflow-hidden border-b-2 border-greyVeryLight bg-white px-5',
                tableStickyHeadActive ? 'flex' : 'hidden',
            )}
        >
            <div className="mx-auto flex w-full max-w-7xl flex-nowrap overflow-hidden">
                <div className="border-r-1 static z-[2] flex h-full min-w-[115px] max-w-[182px] shrink-0 border-greyVeryLight bg-white sm:w-auto sm:min-w-[220px] sm:max-w-none md:min-w-[265px] md:max-w-none lg:min-w-[270px] vl:min-w-[290px]" />
                {props.comparedProducts.map((product, index) => (
                    <div
                        key={`headSticky-${product.uuid}`}
                        className="border-r-1 flex min-w-[calc(182px+12px*2)] max-w-[calc(182px+12px*2)] shrink-0 basis-64 items-center border-greyVeryLight py-3 px-1 sm:min-w-[calc(205px+20px*2)] sm:max-w-[calc(205px+20px*2)]"
                        style={index === 0 ? { marginLeft: -props.tableMarginLeft } : undefined}
                    >
                        <a className="w-16" href={product.slug}>
                            <Image
                                alt={product.mainImage?.name || product.fullName}
                                className="w-auto"
                                height={64}
                                src={product.mainImage?.url}
                                width={64}
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
