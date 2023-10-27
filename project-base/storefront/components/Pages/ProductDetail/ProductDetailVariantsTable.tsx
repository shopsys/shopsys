import { ProductDetailAvailabilityList } from './ProductDetailAvailabilityList';
import { Image } from 'components/Basic/Image/Image';
import { AddToCart } from 'components/Blocks/Product/AddToCart';
import { ProductAvailableStoresCount } from 'components/Blocks/Product/ProductAvailableStoresCount';
import { Popup } from 'components/Layout/Popup/Popup';
import { MainVariantDetailFragmentApi, StoreAvailabilityFragmentApi } from 'graphql/generated';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';

type ProductVariantsTableProps = {
    variants: MainVariantDetailFragmentApi['variants'];
    isSellingDenied: boolean;
};

const TEST_IDENTIFIER = 'pages-productdetail-variant-';

export const ProductVariantsTable: FC<ProductVariantsTableProps> = ({ isSellingDenied, variants }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const [popupStoreAvailabilities, setPopupStoreAvailabilities] = useState<StoreAvailabilityFragmentApi[]>();

    return (
        <>
            <ul className="grid grid-cols-1 gap-2 divide-greyLighter md:grid-cols-2 lg:grid-cols-1 lg:gap-0 lg:divide-y">
                {variants.map((variant, index) => (
                    <li
                        key={variant.uuid}
                        className="mx-auto flex w-full max-w-sm flex-col items-center gap-2 border border-greyLighter p-2 md:max-w-none lg:flex-row lg:border-0 "
                        data-testid={TEST_IDENTIFIER + variant.catalogNumber}
                    >
                        <Image
                            alt={variant.mainImage?.name || variant.fullName}
                            image={variant.mainImage}
                            type="default"
                            wrapperClassName="flex h-48 lg:h-16 lg:w-16"
                        />

                        <div className="flex-1 text-center lg:text-left">{variant.fullName}</div>

                        <div
                            className="flex-1 cursor-pointer text-center lg:text-left"
                            onClick={() => setPopupStoreAvailabilities(variant.storeAvailabilities)}
                        >
                            {variant.availability.name}
                            <ProductAvailableStoresCount
                                availableStoresCount={variant.availableStoresCount}
                                isMainVariant={false}
                            />
                        </div>

                        <div className="lg:w-40 lg:text-right">{formatPrice(variant.price.priceWithVat)}</div>

                        <div className="text-right max-lg:clear-both max-lg:pl-0 lg:w-60">
                            {isSellingDenied ? (
                                t('This item can no longer be purchased')
                            ) : (
                                <AddToCart
                                    gtmMessageOrigin={GtmMessageOriginType.product_detail_page}
                                    gtmProductListName={GtmProductListNameType.product_detail_variants_table}
                                    listIndex={index}
                                    maxQuantity={variant.stockQuantity}
                                    minQuantity={1}
                                    productUuid={variant.uuid}
                                />
                            )}
                        </div>
                    </li>
                ))}
            </ul>

            {!!popupStoreAvailabilities && (
                <Popup className="w-11/12 max-w-2xl" onCloseCallback={() => setPopupStoreAvailabilities(undefined)}>
                    <ProductDetailAvailabilityList storeAvailabilities={popupStoreAvailabilities} />
                </Popup>
            )}
        </>
    );
};
