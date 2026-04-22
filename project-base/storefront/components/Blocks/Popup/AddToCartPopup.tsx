import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { DeferredRecommendedProducts } from 'components/Blocks/Product/DeferredRecommendedProducts';
import { ProductGift } from 'components/Blocks/Product/ProductGift';
import { Button } from 'components/Forms/Button/Button';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { CartItemPrice } from 'components/Pages/Cart/CartItemPrice';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeCartItemWithGiftsFragment } from 'graphql/requests/cart/fragments/CartItemWithGiftsFragment.generated';
import { TypeRecommendationType } from 'graphql/types';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible } from 'utils/mappers/price';
import { generateProductImageAlt } from 'utils/productAltText';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const Popup = dynamic(() => import('components/Layout/Popup/Popup').then((component) => component.Popup));

type AddToCartPopupProps = {
    addedCartItem: TypeCartItemWithGiftsFragment;
    key: string;
};

export const AddToCartPopup: FC<AddToCartPopupProps> = ({ key, addedCartItem: { product, quantity } }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { url, isLuigisBoxActive } = useDomainConfig();
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], url);
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const { restoreStoredFocus } = useSessionStore.getState();

    const productUrl = (product.__typename === 'Variant' && product.mainVariant?.slug) || product.slug;

    const ariaDescription = t(
        'Great choice! We have added your item to the cart. You can now proceed to checkout or continue shopping.',
        { ns: 'accessibility' },
    );

    const handleClosePopup = () => {
        restoreStoredFocus();
        updatePortalContent(null);
    };

    return (
        <Popup
            key={key}
            hideCloseButton
            ariaDescription={ariaDescription}
            className="w-11/12 max-w-5xl"
            contentClassName="overflow-y-auto"
            title={t('Great choice! We have added your item to the cart')}
        >
            <VerticalStack gap={'xs'}>
                <section
                    aria-labelledby={`added-product-${product.uuid}-name`}
                    className="relative flex flex-row flex-wrap vl:flex-nowrap items-center gap-4 rounded-xl bg-background-more p-4 vl:p-5"
                >
                    <div className="flex vl:flex-1 basis-full vl:basis-auto vl:items-center gap-2.5">
                        <div className="flex size-20 shrink-0">
                            <ExtendedNextLink
                                className="relative"
                                href={productUrl}
                                tabIndex={-1}
                                tid={TIDs.add_to_cart_popup_image}
                                type={product.__typename === 'RegularProduct' ? 'product' : 'productMainVariant'}
                            >
                                <Image
                                    alt={generateProductImageAlt(product.fullName, product.categories[0]?.name)}
                                    className="size-20 object-contain mix-blend-multiply"
                                    height={80}
                                    src={product.mainImage?.url}
                                    width={80}
                                />
                            </ExtendedNextLink>
                        </div>

                        <div
                            className="flex vl:w-48 flex-col gap-2 tracking-wide"
                            data-tid={TIDs.blocks_product_addtocartpopup_product_name}
                        >
                            <ExtendedNextLink
                                className="font-secondary font-semibold text-text-default no-underline hover:cursor-pointer hover:text-text-accent hover:underline"
                                href={productUrl}
                                type={product.__typename === 'RegularProduct' ? 'product' : 'productMainVariant'}
                            >
                                <h3 className="text-sm lg:text-sm" id={`added-product-${product.uuid}-name`}>
                                    {product.fullName}
                                </h3>
                            </ExtendedNextLink>

                            <div className="text-sm text-text-less">
                                {t('Code')}: {product.catalogNumber}
                            </div>
                        </div>
                    </div>

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
                </section>

                <ProductGift gifts={product.gifts} />

                <div className="flex flex-col gap-4 text-center md:flex-row md:items-center md:justify-between md:p-0">
                    <Button
                        aria-label={t('Go back to shop', { ns: 'accessibility' })}
                        className="w-full md:w-auto"
                        variant="inverted"
                        onClick={handleClosePopup}
                    >
                        {t('Back to shop')}
                    </Button>

                    <LinkButton
                        aria-label={t('Go to cart', { ns: 'accessibility' })}
                        href={cartUrl}
                        skeletonType="cart"
                        tid={TIDs.popup_go_to_cart_button}
                    >
                        {t('To cart')}
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
