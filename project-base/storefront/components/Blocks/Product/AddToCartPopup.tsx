import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { Image } from 'components/Basic/Image/Image';
import { Link } from 'components/Basic/Link/Link';
import { Button } from 'components/Forms/Button/Button';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { CartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { mapPriceForCalculations } from 'utils/mappers/price';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const Popup = dynamic(() => import('components/Layout/Popup/Popup').then((component) => component.Popup));

type AddToCartPopupProps = {
    onCloseCallback: () => void;
    addedCartItem: CartItemFragment;
};

export const AddToCartPopup: FC<AddToCartPopupProps> = ({ onCloseCallback, addedCartItem: { product, quantity } }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { url } = useDomainConfig();
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], url);

    const productUrl = (product.__typename === 'Variant' && product.mainVariant?.slug) || product.slug;

    return (
        <Popup hideCloseButton className="w-11/12 max-w-2xl" onCloseCallback={onCloseCallback}>
            <div className="mb-4 flex w-full items-center md:mb-6">
                <CheckmarkIcon className="mr-4 w-7 text-greenDark" />
                <div className="h2 text-primary">{t('Great choice! We have added your item to the cart')}</div>
            </div>

            <div className="mb-4 flex flex-col items-center rounded border border-greyLighter p-3 md:flex-row md:p-4">
                {!!product.mainImage && (
                    <div className="mb-4 flex w-24 items-center justify-center md:mb-0">
                        <Image
                            alt={product.mainImage.name || product.fullName}
                            className="max-h-20 w-auto"
                            height={48}
                            src={product.mainImage.url}
                            width={72}
                        />
                    </div>
                )}
                <div className="w-full md:pl-4 lg:flex lg:items-center lg:justify-between">
                    <div
                        className="block break-words text-primary"
                        tid={TIDs.blocks_product_addtocartpopup_product_name}
                    >
                        <ExtendedNextLink
                            href={productUrl}
                            type={product.__typename === 'RegularProduct' ? 'product' : 'productMainVariant'}
                        >
                            {product.fullName}
                        </ExtendedNextLink>
                    </div>

                    <div className="mt-2 lg:mt-0 lg:w-5/12 lg:pl-4 lg:text-right">
                        <div className="block text-primary">
                            {`${quantity} ${product.unit.name}, ${formatPrice(
                                quantity * mapPriceForCalculations(product.price.priceWithVat),
                            )}`}
                        </div>
                    </div>
                </div>
            </div>

            <div className="flex flex-col text-center md:flex-row md:items-center md:justify-between md:p-0">
                <Button className="mt-2 lg:w-auto lg:justify-start" onClick={onCloseCallback}>
                    {t('Back to shop')}
                </Button>

                <Link isButton className="mt-2 w-full lg:w-auto lg:justify-start" href={cartUrl}>
                    {t('To cart')}
                </Link>
            </div>
        </Popup>
    );
};
