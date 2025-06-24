import { AddToCart } from 'components/Blocks/Product/AddToCart';
import { Button } from 'components/Forms/Button/Button';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';

const InquiryPopup = dynamic(
    () => import('components/Blocks/Popup/InquiryPopup').then((component) => component.InquiryPopup),
    {
        ssr: false,
    },
);

type ProductActionProps = {
    product: TypeListedProductFragment;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    listIndex: number;
    isWithSpinbox?: boolean;
    buttonSize?: 'small' | 'medium' | 'large' | 'xlarge';
    buttonVariant?: 'primary' | 'inverted';
    showResponsiveCartIcon?: boolean;
    skipKeyboardNavigation?: boolean;
};

export const ProductAction: FC<ProductActionProps> = ({
    product,
    gtmProductListName,
    gtmMessageOrigin,
    listIndex,
    isWithSpinbox = false,
    buttonSize,
    buttonVariant = 'primary',
    showResponsiveCartIcon = false,
    skipKeyboardNavigation = false,
}) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const { canCreateOrder } = useAuthorization();
    const formatPrice = useFormatPrice();

    if (product.isSellingDenied) {
        return <div className="max-w-[215px] text-center">{t('This item can no longer be purchased')}</div>;
    }

    if (!product.isMainVariant && product.isInquiryType) {
        const openInquiryPopup = (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
            e.stopPropagation();
            updatePortalContent(<InquiryPopup productUuid={product.uuid} />);
        };
        return (
            <Button size={buttonSize} tabIndex={skipKeyboardNavigation ? -1 : 0} onClick={openInquiryPopup}>
                {t('Inquire')}
            </Button>
        );
    }

    if (!canCreateOrder) {
        return null;
    }

    if (product.isMainVariant) {
        return (
            <LinkButton
                href={product.slug}
                tabIndex={skipKeyboardNavigation ? -1 : 0}
                type="productMainVariant"
                aria-label={t('Go to page with product variants of {{ productName }}', {
                    productName: product.fullName,
                })}
            >
                {t('Choose')}
            </LinkButton>
        );
    }

    const ariaLabel = t('Add to cart {{ productName }}, quantity {{ quantity }} {{ unit }} for {{ price }}', {
        productName: product.fullName,
        quantity: 1,
        unit: product.unit.name,
        price: formatPrice(product.price.priceWithVat),
    });

    return (
        <AddToCart
            ariaLabel={ariaLabel}
            buttonSize={buttonSize}
            buttonVariant={buttonVariant}
            gtmMessageOrigin={gtmMessageOrigin}
            gtmProductListName={gtmProductListName}
            isWithSpinbox={isWithSpinbox}
            listIndex={listIndex}
            minQuantity={1}
            productUuid={product.uuid}
            showResponsiveCartIcon={showResponsiveCartIcon}
            tabIndex={skipKeyboardNavigation ? -1 : 0}
        />
    );
};
