import { Image } from 'components/Basic/Image/Image';
import { AdditionalServices } from 'components/Blocks/Product/AdditionalServices/AdditionalServices';
import { ProductAction } from 'components/Blocks/Product/ProductAction';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { TypeProductVariantsQuery } from 'graphql/requests/products/queries/ProductVariantsQuery.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useProductAdditionalServices } from 'utils/cart/useProductAdditionalServices';
import { generateProductImageAlt } from 'utils/productAltText';
import { VARIANT_PURCHASE_CLASS_NAME, VARIANT_ROW_CLASS_NAME } from './productVariantsPopupStyles';

type ProductVariantsPopupRowProps = {
    variant: Extract<
        NonNullable<TypeProductVariantsQuery['product']>,
        { __typename: 'MainVariant' }
    >['variants'][number];
    gtmMessageOrigin: GtmMessageOriginType;
    gtmProductListName: GtmProductListNameType;
    listIndex: number;
};

export const ProductVariantsPopupRow: FC<ProductVariantsPopupRowProps> = ({
    variant,
    gtmMessageOrigin,
    gtmProductListName,
    listIndex,
}) => {
    const {
        selectedServiceUuids,
        isAddToCartFlowPending,
        updateIsAddToCartFlowPending,
        onToggleService,
        persistPendingServicesAfterAddToCart,
        isSettingAdditionalServices,
        cartItemQuantity,
    } = useProductAdditionalServices({ productUuid: variant.uuid, gtmProductListName });

    return (
        <li className={VARIANT_ROW_CLASS_NAME}>
            <div className="size-16 self-start sm:size-20 md:self-center">
                <Image
                    height={80}
                    width={80}
                    alt={generateProductImageAlt(variant.fullName, variant.mainCategory?.name, variant.mainImage?.name)}
                    className="size-16 object-contain mix-blend-multiply sm:size-20"
                    sizes="(min-width: 480px) 80px, 64px"
                    src={variant.mainImage?.url}
                />
            </div>

            <div className="flex min-w-0 flex-1 flex-col gap-2">
                <h3 className="wrap-break-word font-secondary font-semibold text-sm">{variant.fullName}</h3>
                <ProductAvailability
                    availability={variant.availability}
                    availableStoresCount={variant.availableStoresCount}
                    isInquiryType={variant.isInquiryType}
                    isPersonalPickupOnly={variant.isPersonalPickupOnly}
                />
            </div>

            <div className={VARIANT_PURCHASE_CLASS_NAME}>
                <ProductPrice
                    className="tabular-nums md:justify-end"
                    productPrice={variant.price}
                    textPriceSize="base"
                />
                <div className="col-start-1 xs:col-start-2 flex flex-col gap-2 md:col-start-1">
                    <ProductAction
                        gtmMessageOrigin={gtmMessageOrigin}
                        gtmProductListName={gtmProductListName}
                        listIndex={listIndex}
                        product={variant}
                        onAddToCartFlowStateChange={updateIsAddToCartFlowPending}
                        onProductAddedToCart={(addedCartItem) =>
                            persistPendingServicesAfterAddToCart(addedCartItem.uuid)
                        }
                    />
                </div>
            </div>
            <AdditionalServices
                additionalServices={variant.additionalServices}
                className="col-span-2 pt-3 md:col-start-2"
                isDisabled={isAddToCartFlowPending || isSettingAdditionalServices}
                quantity={cartItemQuantity}
                selectedServiceUuids={selectedServiceUuids}
                tidDiscriminator={variant.catalogNumber}
                unitName={variant.unit.name}
                showSelectedServiceTotalPrice
                onToggleService={onToggleService}
            />
        </li>
    );
};
