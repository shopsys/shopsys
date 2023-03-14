import { Image } from 'components/Basic/Image/Image';
import { ComparedProductFragmentApi } from 'graphql/generated';
import { useHandleCompareTable } from 'hooks/product/useHandleCompareTable';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { twJoin } from 'tailwind-merge';

type HeadStickyProps = {
    productsCompare: ComparedProductFragmentApi[];
    tableMarginLeft: number;
};

export const HeadSticky: FC<HeadStickyProps> = (props) => {
    const { tableStickyHeadActive } = useHandleCompareTable(props.productsCompare.length);
    const t = useTypedTranslationFunction();

    return (
        <div
            className={twJoin(
                'fixed top-0 left-0 z-[2] w-full overflow-hidden bg-white px-5',
                tableStickyHeadActive ? 'flex' : 'hidden',
            )}
        >
            <div className="mx-auto flex w-full max-w-7xl flex-nowrap border-b-2 border-greyVeryLight">
                <div className="border-r-1 static z-[2] flex h-full min-w-[115px] max-w-[182px] shrink-0 border-greyVeryLight bg-white sm:w-auto sm:min-w-[211px] sm:max-w-none lg:min-w-[298px]" />
                {props.productsCompare.map((product, index) => (
                    <div
                        className="border-r-1 flex min-w-[calc(182px+12px*2)] max-w-[calc(182px+12px*2)] shrink-0 basis-64 items-center border-greyVeryLight py-3 px-1 sm:min-w-[calc(211px+20px*2)] sm:max-w-[calc(211px+20px*2)]"
                        key={`headSticky-${product.uuid}`}
                        style={index === 0 ? { marginLeft: -props.tableMarginLeft } : undefined}
                    >
                        <a className="w-16" href={product.slug}>
                            <Image image={product.image} type="listVerySmall" alt={product.fullName} />
                        </a>
                        <div className="ml-2 flex flex-1 flex-col">
                            <div className="text-xs">
                                {t('Code')}: {product.catalogNumber}
                            </div>
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
