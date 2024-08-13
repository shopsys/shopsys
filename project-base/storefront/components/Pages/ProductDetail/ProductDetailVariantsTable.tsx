import { Image } from 'components/Basic/Image/Image';
import { AddToCart } from 'components/Blocks/Product/AddToCart';
import { ProductAvailableStoresCount } from 'components/Blocks/Product/ProductAvailableStoresCount';
import { TIDs } from 'cypress/tids';
import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';

const ProductVariantsAvailabilityPopup = dynamic(
    () =>
        import('components/Blocks/Popup/ProductVariantsAvailabilityPopup').then(
            (component) => component.ProductVariantsAvailabilityPopup,
        ),
    {
        ssr: false,
    },
);

type ProductVariantsTableProps = {
    variants: TypeMainVariantDetailFragment['variants'];
    isSellingDenied: boolean;
};

export const ProductVariantsTable: FC<ProductVariantsTableProps> = ({ isSellingDenied, variants }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    if (variants.length === 0) {
        return <p>{t('Currently, it is not possible to purchase any variant of this product.')}</p>;
    }

    return (
        <ul className="grid grid-cols-1 gap-2 divide-borderAccent md:grid-cols-2 lg:grid-cols-1 lg:gap-0 lg:divide-y">
            {variants.map((variant, index) => (
                <li
                    key={variant.uuid}
                    className="mx-auto flex w-full max-w-sm flex-col items-center gap-2 border border-borderAccent p-2 md:max-w-none lg:flex-row lg:border-0 "
                    tid={TIDs.pages_productdetail_variant_ + variant.catalogNumber}
                >
                    <div className="relative h-48 w-full lg:h-16 lg:w-16" tid={TIDs.product_detail_main_image}>
                        <Image
                            fill
                            priority
                            alt={variant.mainImage?.name || variant.fullName}
                            className="object-contain"
                            sizes="(max-width: 600px) 100vw, (max-width: 768px) 50vw, 8vw"
                            src={variant.mainImage?.url}
                        />
                    </div>

                    <div className="flex-1 text-center lg:text-left">{variant.fullName}</div>

                    <div
                        className="flex-1 cursor-pointer text-center lg:text-left"
                        onClick={() => {
                            updatePortalContent(
                                <ProductVariantsAvailabilityPopup storeAvailabilities={variant.storeAvailabilities} />,
                            );
                        }}
                    >
                        {variant.availability.name}
                        <ProductAvailableStoresCount
                            availableStoresCount={variant.availableStoresCount}
                            isMainVariant={false}
                        />
                    </div>

                    {isPriceVisible(variant.price.priceWithVat) && (
                        <div className="lg:w-40 lg:text-right">{formatPrice(variant.price.priceWithVat)}</div>
                    )}

                    <div className="text-right max-lg:clear-both">
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
    );
};
