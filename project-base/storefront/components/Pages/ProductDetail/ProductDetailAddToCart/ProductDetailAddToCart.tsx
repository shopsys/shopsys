import { Loader } from 'components/Basic/Loader/Loader';
import { Button } from 'components/Forms/Button/Button';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TIDs } from 'cypress/tids';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useRef } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { useAddToCart } from 'utils/cart/useAddToCart';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { mapPriceForCalculations } from 'utils/mappers/price';

const AddToCartPopup = dynamic(
    () => import('components/Blocks/Popup/AddToCartPopup').then((component) => component.AddToCartPopup),
    { ssr: false },
);

const InquiryPopup = dynamic(
    () => import('components/Blocks/Popup/InquiryPopup').then((component) => component.InquiryPopup),
    {
        ssr: false,
    },
);

export type ProductDetailAddToCartProps = {
    product: TypeProductDetailFragment;
};

export const ProductDetailAddToCart: FC<ProductDetailAddToCartProps> = ({ product }) => {
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const { t } = useTranslation();
    const { addToCart, isAddingToCart } = useAddToCart(
        GtmMessageOriginType.product_detail_page,
        GtmProductListNameType.product_detail,
    );
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const { canCreateOrder } = useAuthorization();
    const formatPrice = useFormatPrice();

    const onAddToCartHandler = async () => {
        if (!spinboxRef.current) {
            return;
        }

        const addToCartResult = await addToCart(product.uuid, spinboxRef.current.valueAsNumber);
        spinboxRef.current!.valueAsNumber = 1;

        if (addToCartResult) {
            updatePortalContent(
                <AddToCartPopup
                    key={addToCartResult.addProductResult.cartItem.uuid}
                    addedCartItem={addToCartResult.addProductResult.cartItem}
                />,
            );
        }
    };

    if (product.isSellingDenied) {
        return <p className="text-text-error">{t('This item can no longer be purchased')}</p>;
    }

    if (product.isInquiryType) {
        const openInquiryPopup = (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
            e.stopPropagation();
            updatePortalContent(<InquiryPopup productUuid={product.uuid} />);
        };

        return (
            <Button
                aria-haspopup="dialog"
                aria-label={t('Open inquiry popup')}
                className="w-fit"
                size="large"
                title={t('Inquire popup')}
                onClick={openInquiryPopup}
            >
                {t('Inquire')}
            </Button>
        );
    }

    if (!canCreateOrder) {
        return null;
    }

    const isWatchdogButtonVisible =
        (product.uuid && product.availability.status === TypeAvailabilityStatusEnum.OutOfStock) ||
        product.isSellingDenied;

    const ariaLabel = t('Add to cart {{ productName }}, quantity {{ quantity }} {{ unit }} for {{ price }}', {
        productName: product.name,
        quantity: spinboxRef.current?.valueAsNumber,
        unit: product.unit.name,
        price: formatPrice(
            (spinboxRef.current?.valueAsNumber ?? 1) * mapPriceForCalculations(product.price.priceWithVat),
        ),
    });

    return (
        <div className="flex items-center gap-2">
            <Spinbox defaultValue={1} id={product.uuid} min={1} ref={spinboxRef} size="xlarge" step={1} />

            <div className="relative">
                {isAddingToCart && (
                    <Loader className="z-overlay bg-background-more absolute inset-0 flex h-full w-full items-center justify-center rounded-sm py-2 opacity-50" />
                )}

                <Button
                    aria-haspopup="dialog"
                    aria-label={ariaLabel}
                    className="whitespace-nowrap"
                    isDisabled={isAddingToCart}
                    size="xlarge"
                    tid={TIDs.pages_productdetail_addtocart_button}
                    title={t('Add to cart')}
                    variant={isWatchdogButtonVisible ? 'inverted' : 'primary'}
                    onClick={onAddToCartHandler}
                >
                    {t('Add to cart')}
                </Button>
            </div>
        </div>
    );
};
