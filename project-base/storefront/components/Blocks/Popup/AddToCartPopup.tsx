import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { DeferredRecommendedProducts } from 'components/Blocks/Product/DeferredRecommendedProducts';
import { ProductGift } from 'components/Blocks/Product/ProductGift';
import { Button } from 'components/Forms/Button/Button';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeCartItemWithGiftsFragment } from 'graphql/requests/cart/fragments/CartItemWithGiftsFragment.generated';
import { TypeRecommendationType } from 'graphql/types';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';
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
            <div className="border-border-default mb-4 flex flex-col items-center rounded-sm border p-3 md:flex-row md:p-4">
                {!!product.mainImage && (
                    <div
                        className="mb-4 flex h-12 w-24 items-center justify-center md:mb-0"
                        data-tid={TIDs.add_to_cart_popup_image}
                    >
                        <Image
                            alt={generateProductImageAlt(product.fullName, product.categories[0]?.name)}
                            className="max-h-12 w-auto"
                            height={48}
                            src={product.mainImage.url}
                            width={72}
                        />
                    </div>
                )}
                <div className="w-full md:pl-4 lg:flex lg:items-center lg:justify-between">
                    <div className="block wrap-break-word" data-tid={TIDs.blocks_product_addtocartpopup_product_name}>
                        <ExtendedNextLink
                            href={productUrl}
                            type={product.__typename === 'RegularProduct' ? 'product' : 'productMainVariant'}
                        >
                            {product.fullName}
                        </ExtendedNextLink>
                    </div>

                    <div className="mt-2 lg:mt-0 lg:w-5/12 lg:pl-4 lg:text-right">
                        <div className="text-price-default block">
                            {`${quantity} ${product.unit.name}`}
                            {isPriceVisible(product.price.priceWithVat) &&
                                `, ${formatPrice(quantity * mapPriceForCalculations(product.price.priceWithVat))}`}
                        </div>
                    </div>
                </div>
            </div>
            <div>
                {product.gifts.length > 0 && (
                    <>
                        <p className="h3 mb-3">{t('Gifts')}</p>
                        {product.gifts.map((gift) => (
                            <ProductGift key={gift.uuid} gift={gift} />
                        ))}
                    </>
                )}
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
        </Popup>
    );
};
