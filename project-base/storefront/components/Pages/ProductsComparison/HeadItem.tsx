import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Icon } from 'components/Basic/Icon/Icon';
import { Image } from 'components/Basic/Image/Image';
import { ProductAction } from 'components/Blocks/Product/ProductAction';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { ComparedProductFragmentApi, ListedProductFragmentApi } from 'graphql/generated';
import { onGtmProductClickEventHandler } from 'helpers/gtm/eventHandlers';
import { useComparisonTable } from 'hooks/comparison/useComparisonTable';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useCallback } from 'react';
import { GtmMessageOriginType, GtmProductListNameType } from 'types/gtm/enums';

type ItemProps = {
    product: ComparedProductFragmentApi;
    productsCompareCount: number;
    listIndex: number;
    toggleProductInComparison: () => void;
};

export const HeadItem: FC<ItemProps> = ({ product, productsCompareCount, listIndex, toggleProductInComparison }) => {
    const t = useTypedTranslationFunction();
    const { url } = useDomainConfig();
    const { calcMaxMarginLeft } = useComparisonTable(productsCompareCount);

    const onProductDetailRedirectHandler = useCallback(
        (product: ListedProductFragmentApi, listName: GtmProductListNameType, index: number) => {
            onGtmProductClickEventHandler(product, listName, index, url);
        },
        [url],
    );

    return (
        <th className="relative px-3 pb-3 align-top sm:px-5 sm:pb-5" id="js-table-compare-product">
            <div className="flex h-[365px] w-[182px] flex-col gap-2 sm:w-[205px]">
                <div className="flex flex-col items-center ">
                    <div className="flex h-[185px] w-full items-center justify-center pt-4 pb-3">
                        <Image
                            image={product.mainImage}
                            type="list"
                            alt={product.mainImage?.name || product.fullName}
                        />
                    </div>
                    <ExtendedNextLink
                        href={product.slug}
                        type="product"
                        className="text-primary no-underline hover:no-underline"
                        onClick={() =>
                            onProductDetailRedirectHandler(
                                product,
                                GtmProductListNameType.product_comparison_page,
                                listIndex,
                            )
                        }
                    >
                        {product.fullName}
                    </ExtendedNextLink>
                    <p className="mb-2 text-xs">
                        {t('Code')}: {product.catalogNumber}
                    </p>
                </div>
                <div className="mt-auto">
                    <ProductAction
                        product={product}
                        gtmProductListName={GtmProductListNameType.product_comparison_page}
                        gtmMessageOrigin={GtmMessageOriginType.other}
                        listIndex={listIndex}
                    />
                </div>
            </div>
            <div
                className="absolute top-1 right-1 flex h-10 w-10 cursor-pointer items-center justify-center rounded bg-white transition-colors hover:bg-greyVeryLight"
                onClick={() => {
                    toggleProductInComparison();
                    calcMaxMarginLeft();
                }}
            >
                <Icon className="w-4 text-grey" iconType="icon" icon="Remove" />
            </div>

            {product.flags.length > 0 && (
                <div className="absolute left-0 top-0 mt-7 flex flex-col items-start">
                    <ProductFlags flags={product.flags} />
                </div>
            )}
        </th>
    );
};
