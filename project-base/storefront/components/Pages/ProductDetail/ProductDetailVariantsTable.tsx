import { Image } from 'components/Basic/Image/Image';
import { useOpenDeliveryOptionsPopup } from 'components/Blocks/Popup/DeliveryOptionsPopup/useOpenDeliveryOptionsPopup';
import { AdditionalServices } from 'components/Blocks/Product/AdditionalServices/AdditionalServices';
import { ProductAction } from 'components/Blocks/Product/ProductAction';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { ProductListItemButtons } from 'components/Blocks/Product/ProductsList/ProductListItemActions';
import { PRODUCT_VARIANTS_ID } from 'components/Blocks/Product/productActionConstants';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { ReactNode } from 'react';
import { useProductAdditionalServices } from 'utils/cart/useProductAdditionalServices';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showProductDeliveryOptionsPopup } from 'utils/product/showProductDeliveryOptionsPopup';
import { generateProductImageAlt } from 'utils/productAltText';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';

type ProductVariant = TypeMainVariantDetailFragment['variants'][number];

type ProductVariantsTableProps = {
    deliveryOptionsProducts: TypeMainVariantDetailFragment['variants'];
    variants: TypeMainVariantDetailFragment['variants'];
};

export const ProductVariantsTable: FC<ProductVariantsTableProps> = ({ deliveryOptionsProducts, variants }) => {
    const { t } = useTranslation();
    const openDeliveryOptionsPopup = useOpenDeliveryOptionsPopup();
    const { isProductInComparison, toggleProductInComparison } = useComparison();
    const { isProductInWishlist, toggleProductInWishlist } = useWishlist();

    if (variants.length === 0) {
        return <p>{t('Currently, it is not possible to purchase any variant of this product.')}</p>;
    }

    return (
        <Webline>
            <ul
                className="grid scroll-mt-fixed-header grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-1"
                id={PRODUCT_VARIANTS_ID}
                tabIndex={-1}
            >
                {variants.map((variant, index) => (
                    <ProductVariantRow
                        key={variant.uuid}
                        index={index}
                        variant={variant}
                        listActions={
                            <ProductListItemButtons
                                allowKeyboardFocus
                                productName={variant.fullName}
                                isProductInComparison={isProductInComparison(variant.uuid)}
                                isProductInWishlist={isProductInWishlist(variant.uuid)}
                                toggleProductInComparison={() =>
                                    toggleProductInComparison(
                                        variant,
                                        GtmProductListNameType.product_detail_variants_table,
                                        index,
                                    )
                                }
                                toggleProductInWishlist={() =>
                                    toggleProductInWishlist(
                                        variant,
                                        GtmProductListNameType.product_detail_variants_table,
                                        index,
                                    )
                                }
                            />
                        }
                        onAvailabilityClick={() => openDeliveryOptionsPopup(deliveryOptionsProducts, variant.uuid)}
                    />
                ))}
            </ul>
        </Webline>
    );
};

type ProductVariantRowProps = {
    variant: ProductVariant;
    index: number;
    listActions: ReactNode;
    onAvailabilityClick: () => void;
};

const ProductVariantRow: FC<ProductVariantRowProps> = ({ variant, index, onAvailabilityClick, listActions }) => {
    const {
        selectedServiceUuids,
        onToggleService,
        persistPendingServicesAfterAddToCart,
        isSettingAdditionalServices,
        cartItemQuantity,
    } = useProductAdditionalServices({
        productUuid: variant.uuid,
        gtmProductListName: GtmProductListNameType.product_detail_variants_table,
    });

    return (
        <li
            className="mx-auto flex w-full max-w-sm flex-col gap-2 rounded-xl bg-background-more p-4 vl:p-5 md:max-w-none"
            data-tid={TIDs.pages_productdetail_variant_ + variant.catalogNumber}
        >
            <div className="flex w-full flex-col items-center gap-2 lg:grid lg:grid-cols-[4rem_minmax(0,1fr)_minmax(10rem,1fr)_auto] lg:gap-4">
                <div
                    className="relative h-48 w-full shrink-0 lg:h-16 lg:w-16"
                    data-tid={TIDs.product_detail_main_image}
                >
                    <Image
                        fill
                        priority
                        alt={generateProductImageAlt(
                            variant.fullName,
                            variant.mainCategory?.name,
                            variant.mainImage?.name,
                        )}
                        className="object-contain mix-blend-multiply"
                        sizes="(max-width: 599px) 100vw, (max-width: 768px) 50vw, 64px"
                        src={variant.mainImage?.url}
                    />
                </div>

                <div className="wrap-break-word line-clamp-2 min-h-10 min-w-0 text-center font-secondary font-semibold text-sm lg:line-clamp-none lg:min-h-fit lg:text-left">
                    {variant.fullName}
                </div>

                <ProductVariantAvailability variant={variant} onClick={onAvailabilityClick} />

                <div className="flex w-full min-w-0 flex-col items-center gap-2 lg:w-auto lg:items-end">
                    <ProductPrice className="justify-center lg:justify-end" productPrice={variant.price} />

                    <div className="flex w-full flex-wrap items-center justify-center gap-2 lg:w-auto lg:justify-end">
                        <div className="flex shrink-0 items-center">{listActions}</div>
                        <div className="flex w-40 shrink-0 flex-col gap-2 sm:w-44">
                            <ProductAction
                                buttonSize="medium"
                                isWatchdogButtonShownWithPurchaseAction
                                gtmMessageOrigin={GtmMessageOriginType.product_detail_page}
                                gtmProductListName={GtmProductListNameType.product_detail_variants_table}
                                listIndex={index}
                                product={variant}
                                onProductAddedToCart={(addedCartItem) =>
                                    persistPendingServicesAfterAddToCart(addedCartItem.uuid)
                                }
                            />
                        </div>
                    </div>
                </div>
            </div>

            <AdditionalServices
                additionalServices={variant.additionalServices}
                className="lg:max-w-lg lg:pl-18"
                isDisabled={isSettingAdditionalServices}
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

type ProductVariantAvailabilityProps = {
    onClick: () => void;
    variant: ProductVariant;
};

const ProductVariantAvailability: FC<ProductVariantAvailabilityProps> = ({ onClick, variant }) => {
    if (variant.isSellingDenied) {
        return null;
    }

    const productAvailability = (
        <ProductAvailability
            availability={variant.availability}
            availableStoresCount={variant.availableStoresCount}
            isPersonalPickupOnly={variant.isPersonalPickupOnly}
            displayMode="detail"
            isInquiryType={variant.isInquiryType}
            className="min-w-40 items-center lg:items-start"
            detailsClassName="pl-0 text-xs lg:pl-5"
        />
    );

    if (!showProductDeliveryOptionsPopup(variant)) {
        return productAvailability;
    }

    return (
        <button
            aria-haspopup="dialog"
            className="cursor-pointer rounded-md text-left lg:justify-self-start"
            type="button"
            onClick={onClick}
        >
            {productAvailability}
        </button>
    );
};
