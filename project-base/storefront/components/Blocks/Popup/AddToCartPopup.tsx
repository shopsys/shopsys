import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { AdditionalServices } from 'components/Blocks/Product/AdditionalServices/AdditionalServices';
import { DeferredRecommendedProducts } from 'components/Blocks/Product/DeferredRecommendedProducts';
import { ProductGift } from 'components/Blocks/Product/ProductGift';
import { Button } from 'components/Forms/Button/Button';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { Popup } from 'components/Layout/Popup/Popup';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { CartItemPrice } from 'components/Pages/Cart/CartItemPrice';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeCartItemWithGiftsFragment } from 'graphql/requests/cart/fragments/CartItemWithGiftsFragment.generated';
import { TypeRecommendationType } from 'graphql/types';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useSessionStore } from 'store/useSessionStore';
import { useProductAdditionalServices } from 'utils/cart/useProductAdditionalServices';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible } from 'utils/mappers/price';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type AddToCartPopupProps = {
    addedCartItem: TypeCartItemWithGiftsFragment;
};

export const AddToCartPopup: FC<AddToCartPopupProps> = ({ addedCartItem: { product, quantity } }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { url, isLuigisBoxActive } = useDomainConfig();
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], url);
    const closePortalContent = useSessionStore((s) => s.closePortalContent);
    const { selectedServiceUuids, onToggleService, isSettingAdditionalServices, cartItemQuantity } =
        useProductAdditionalServices({
            productUuid: product.uuid,
            gtmProductListName: GtmProductListNameType.cart,
        });

    const productUrl = (product.__typename === 'Variant' && product.mainVariant?.slug) || product.slug;

    const ariaDescription = t(
        'Great choice! We have added your item to the cart. You can now proceed to checkout or continue shopping.',
        { ns: 'accessibility' },
    );

    const handleClosePopup = () => {
        closePortalContent();
    };

    return (
        <Popup
            hideCloseButton
            ariaDescription={ariaDescription}
            className="max-h-[calc(100dvh-2rem)] vl:max-h-[85vh] w-11/12 max-w-5xl overflow-hidden"
            contentClassName="-mx-5 -mb-5 min-h-0 overflow-y-auto px-5 vl:m-0 vl:px-0 vl:pb-2"
            title={t('Great choice! We have added your item to the cart')}
        >
            <VerticalStack gap={'xs'}>
                <section
                    aria-labelledby={`added-product-${product.uuid}-name`}
                    className="relative rounded-xl bg-background-more p-4 vl:p-5"
                >
                    <div className="flex flex-row flex-wrap vl:flex-nowrap items-center gap-4">
                        <ExtendedNextLink
                            className="group/product-link flex vl:flex-1 basis-full vl:basis-auto cursor-pointer vl:items-center gap-4 text-text-default no-underline hover:text-text-default hover:no-underline focus-visible:outline-hidden"
                            data-focus-color="preserve"
                            href={productUrl}
                            type={product.__typename === 'RegularProduct' ? 'product' : 'productMainVariant'}
                            aria-label={t('Go to product page of {{ productName }}', {
                                ns: 'accessibility',
                                productName: product.fullName,
                            })}
                        >
                            <div className="flex size-20 shrink-0" data-tid={TIDs.add_to_cart_popup_image}>
                                <Image
                                    alt=""
                                    className="size-20 object-contain mix-blend-multiply"
                                    height={80}
                                    src={product.mainImage?.url}
                                    width={80}
                                />
                            </div>

                            <div
                                className="flex min-w-0 flex-1 flex-col gap-1 tracking-wide"
                                data-tid={TIDs.blocks_product_addtocartpopup_product_name}
                            >
                                <h3
                                    className="font-secondary font-semibold text-sm group-hover/product-link:underline lg:text-sm"
                                    id={`added-product-${product.uuid}-name`}
                                >
                                    <span className="-mx-1 rounded-sm box-decoration-clone px-1 group-focus-visible/product-link:bg-orange-500 group-focus-visible/product-link:text-text-default!">
                                        {product.fullName}
                                    </span>
                                </h3>

                                <div className="text-sm text-text-less">
                                    {t('Code')}: {product.catalogNumber}
                                </div>
                            </div>
                        </ExtendedNextLink>

                        <div className="flex flex-1 items-center justify-end gap-4 vl:gap-6">
                            <div className="font-secondary">
                                <span className="font-semibold">{quantity}</span>
                                <span className="text-sm text-text-less">&nbsp;{product.unit.name}</span>
                            </div>

                            {isPriceVisible(product.price.priceWithVat) && (
                                <div className="whitespace-nowrap font-secondary">
                                    <span className="font-semibold">{formatPrice(product.price.priceWithVat)}</span>
                                    <span className="text-sm text-text-less">&nbsp;/&nbsp;{product.unit.name}</span>
                                </div>
                            )}

                            <CartItemPrice
                                freeQuantity={0}
                                productPrice={product.price}
                                quantity={quantity}
                                className="ml-auto vl:ml-0 vl:w-auto"
                            />
                        </div>
                    </div>

                    <AdditionalServices
                        additionalServices={product.additionalServices}
                        className="mt-2 w-full vl:max-w-lg vl:pl-24"
                        isDisabled={isSettingAdditionalServices}
                        quantity={cartItemQuantity}
                        selectedServiceUuids={selectedServiceUuids}
                        showSelectedServiceTotalPrice
                        tidDiscriminator={product.catalogNumber}
                        unitName={product.unit.name}
                        onToggleService={onToggleService}
                    />
                </section>

                <ProductGift gifts={product.gifts} />

                <div className="vl:static sticky bottom-0 z-10 order-last vl:order-0 -mx-5 vl:mx-0 flex items-center justify-between gap-4 bg-background-default px-5 vl:px-0 vl:py-0 pt-3 pb-5 shadow-[0_-4px_16px_rgba(0,0,0,0.1)] vl:shadow-none">
                    <Button
                        aria-label={t('Go back to shop', { ns: 'accessibility' })}
                        variant="tertiary"
                        onClick={handleClosePopup}
                    >
                        {t('Back to shop')}
                    </Button>

                    <LinkButton
                        aria-label={t('Go to cart', { ns: 'accessibility' })}
                        href={cartUrl}
                        skeletonType="cart"
                        tid={TIDs.popup_go_to_cart_button}
                        size="large"
                    >
                        {t('Continue to cart')}
                    </LinkButton>
                </div>

                {isLuigisBoxActive && (
                    <DeferredRecommendedProducts
                        itemUuids={[product.uuid]}
                        recommendationType={TypeRecommendationType.BasketPopup}
                        render={(recommendedProductsContent) => (
                            <section>
                                <p className="h3 mb-3">{t('Recommended for you')}</p>
                                {recommendedProductsContent}
                            </section>
                        )}
                    />
                )}
            </VerticalStack>
        </Popup>
    );
};
