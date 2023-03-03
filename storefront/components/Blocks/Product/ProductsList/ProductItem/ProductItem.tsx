import { Image } from 'components/Basic/Image/Image';
import { ProductAction } from 'components/Blocks/Product/Action/ProductAction';
import { ProductAvailableStoresCount } from 'components/Blocks/Product/Availability/ProductAvailableStoresCount';
import { ProductExposedStoresCount } from 'components/Blocks/Product/Availability/ProductExposedStoresCount';
import { ButtonsAction } from 'components/Blocks/Product/ButtonsAction/ButtonsAction';
import { ProductFlags } from 'components/Blocks/Product/Flags/ProductFlags';
import { ProductPrice } from 'components/Blocks/Product/Price/ProductPrice';
import { ListedProductFragmentApi } from 'graphql/generated';
import { onClickProductDetailGtmEventHandler } from 'helpers/gtm/eventHandlers';
import NextLink from 'next/link';
import { useCallback } from 'react';
import { useShopsysSelector } from 'redux/main';
import { GtmListNameType } from 'types/gtm';

type ProductItemProps = {
    product: ListedProductFragmentApi;
    listIndex: number;
    gtmListName: GtmListNameType;
};

const getDataTestId = (catalogNumber: string) => 'blocks-product-list-listeditem-' + catalogNumber;

export const ProductItem: FC<ProductItemProps> = ({ product, listIndex, gtmListName }) => {
    const { url } = useShopsysSelector((state) => state.domain);

    const onProductDetailRedirectHandler = useCallback(
        async (product: ListedProductFragmentApi, listName: GtmListNameType, index: number) => {
            await onClickProductDetailGtmEventHandler(product, listName, index, url);
        },
        [url],
    );

    return (
        <div className="border-greyLighter pl-2 pt-6 vl:border-t" data-testid={getDataTestId(product.catalogNumber)}>
            <div className="relative flex h-full flex-col rounded-xl text-left lg:hover:z-above lg:hover:bg-white lg:hover:shadow-xl">
                <NextLink href={product.slug} passHref>
                    <a
                        className="relative flex h-full flex-col no-underline hover:no-underline"
                        onClick={() => onProductDetailRedirectHandler(product, gtmListName, listIndex)}
                    >
                        <div className="relative flex h-[185px] w-full items-center justify-center px-3 pt-4 pb-3">
                            <Image
                                image={product.image}
                                type="list"
                                alt={product.fullName}
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
                <ButtonsAction productUuid={product.uuid} isMainVariant={product.isMainVariant}></ButtonsAction>
                <ProductAction product={product} gtmListName={gtmListName} listIndex={listIndex} />
            </div>
        </div>
    );
};
