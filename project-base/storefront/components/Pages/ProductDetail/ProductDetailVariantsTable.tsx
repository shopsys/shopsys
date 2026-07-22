import { Image } from 'components/Basic/Image/Image';
import { useOpenDeliveryOptionsPopup } from 'components/Blocks/Popup/DeliveryOptionsPopup/useOpenDeliveryOptionsPopup';
import { AdditionalServices } from 'components/Blocks/Product/AdditionalServices/AdditionalServices';
import { PRODUCT_VARIANTS_ID, ProductAction } from 'components/Blocks/Product/ProductAction';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useProductAdditionalServices } from 'utils/cart/useProductAdditionalServices';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isProductSellable } from 'utils/product/isProductSellable';

type ProductVariant = TypeMainVariantDetailFragment['variants'][number];

type ProductVariantsTableProps = {
    deliveryOptionsProducts: TypeMainVariantDetailFragment['variants'];
    variants: TypeMainVariantDetailFragment['variants'];
};

export const ProductVariantsTable: FC<ProductVariantsTableProps> = ({ deliveryOptionsProducts, variants }) => {
    const { t } = useTranslation();
    const openDeliveryOptionsPopup = useOpenDeliveryOptionsPopup();

    if (variants.length === 0) {
        return <p>{t('Currently, it is not possible to purchase any variant of this product.')}</p>;
    }

    return (
        <Webline>
            <ul
                className="grid scroll-mt-fixed-header-with-navigation grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-1"
                id={PRODUCT_VARIANTS_ID}
            >
                {variants.map((variant, index) => (
                    <ProductVariantRow
                        key={variant.uuid}
                        index={index}
                        variant={variant}
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
    onAvailabilityClick: () => void;
};

const ProductVariantRow: FC<ProductVariantRowProps> = ({ variant, index, onAvailabilityClick }) => {
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
            <div className="flex w-full flex-col items-center justify-between gap-2 lg:flex-row">
                <div
                    className="relative h-48 w-full shrink-0 lg:h-16 lg:w-16"
                    data-tid={TIDs.product_detail_main_image}
                >
                    <Image
                        fill
                        priority
                        alt={variant.mainImage?.name || variant.fullName}
                        className="object-contain"
                        sizes="(max-width: 599px) 100vw, (max-width: 768px) 50vw, 64px"
                        src={variant.mainImage?.url}
                    />
                </div>

                <div className="line-clamp-2 min-h-10 text-center font-secondary font-semibold text-sm group-hover:text-link group-hover:underline lg:line-clamp-none lg:min-h-fit lg:w-80 lg:text-left">
                    {variant.fullName}
                </div>

                <ProductVariantAvailability variant={variant} onClick={onAvailabilityClick} />

                <div className="flex flex-col items-center justify-end gap-2.5 lg:ml-auto lg:min-w-96 lg:flex-row">
                    <ProductPrice className="lg:flex-col lg:items-end" productPrice={variant.price} />

                    <div className="flex w-45 flex-col gap-2">
                        <ProductAction
                            buttonSize="large"
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
            detailsClassName="pl-0 lg:pl-5"
        />
    );

    if (!isProductSellable(variant)) {
        return productAvailability;
    }

    return (
        <button aria-haspopup="dialog" className="cursor-pointer rounded-md" type="button" onClick={onClick}>
            {productAvailability}
        </button>
    );
};
