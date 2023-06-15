import { ProductCompareButton } from '../ButtonsAction/ProductCompareButton';
import { Image } from 'components/Basic/Image/Image';
import { ProductAction } from 'components/Blocks/Product/ProductAction';
import { ProductAvailableStoresCount } from 'components/Blocks/Product/ProductAvailableStoresCount';
import { ProductExposedStoresCount } from 'components/Blocks/Product/ProductExposedStoresCount';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { ListedProductFragmentApi } from 'graphql/generated';
import { onGtmProductClickEventHandler } from 'helpers/gtm/eventHandlers';
import { useDomainConfig } from 'hooks/useDomainConfig';
import NextLink from 'next/link';
import { GtmMessageOriginType, GtmProductListNameType } from 'types/gtm/enums';

type ProductItemProps = {
    product: ListedProductFragmentApi;
    listIndex: number;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
};

const getDataTestId = (catalogNumber: string) => 'blocks-product-list-listeditem-' + catalogNumber;

export const ProductItem: FC<ProductItemProps> = ({ product, listIndex, gtmProductListName, gtmMessageOrigin }) => {
    const { url } = useDomainConfig();

    return (
        <div
            className="relative flex flex-col justify-between rounded-t-xl border-greyLighter p-3 text-left lg:hover:z-above lg:hover:bg-white lg:hover:shadow-xl vl:border-b"
            data-testid={getDataTestId(product.catalogNumber)}
        >
            <NextLink href={product.slug} passHref>
                <a
                    className="relative flex h-full flex-col no-underline hover:no-underline"
                    onClick={() => onGtmProductClickEventHandler(product, gtmProductListName, listIndex, url)}
                >
                    <div className="relative flex h-[185px] w-full items-center justify-center px-3 pt-4 pb-3">
                        <Image
                            image={product.image}
                            type="list"
                            alt={product.image?.name || product.fullName}
                            className="max-h-full lg:hover:mix-blend-multiply"
                        />
                        <div className="absolute top-3 left-4 flex flex-col">
                            <ProductFlags flags={product.flags} />
                        </div>
                    </div>
                    <div className="mt-auto flex-1 px-3 pb-5">
                        <h3 className="mb-1 block h-10 overflow-hidden break-words text-lg font-bold leading-5 text-black">
                            {product.fullName}
                        </h3>
                        <ProductPrice productPrice={product.price} />
                        <div className="mb-3 text-sm text-black">
                            {product.availability.name}
                            <ProductAvailableStoresCount
                                isMainVariant={product.isMainVariant}
                                availableStoresCount={product.availableStoresCount}
                            />
                            <ProductExposedStoresCount
                                isMainVariant={product.isMainVariant}
                                exposedStoresCount={product.exposedStoresCount}
                            />
                        </div>
                    </div>
                </a>
            </NextLink>
            <ProductCompareButton
                className="mb-2 justify-end"
                productUuid={product.uuid}
                isMainVariant={product.isMainVariant}
            />
            <ProductAction
                product={product}
                gtmProductListName={gtmProductListName}
                gtmMessageOrigin={gtmMessageOrigin}
                listIndex={listIndex}
            />
        </div>
    );
};
