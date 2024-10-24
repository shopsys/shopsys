import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { AddToCart } from 'components/Blocks/Product/AddToCart';
import { Button } from 'components/Forms/Button/Button';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';

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
    buttonSize?: 'small' | 'medium' | 'large';
};

export const ProductAction: FC<ProductActionProps> = ({
    product,
    gtmProductListName,
    gtmMessageOrigin,
    listIndex,
    isWithSpinbox = false,
    buttonSize,
}) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    if (product.isSellingDenied) {
        return <div className="text-center">{t('This item can no longer be purchased')}</div>;
    }

    if (product.isInquiryType) {
        const openInquiryPopup = (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
            e.stopPropagation();
            updatePortalContent(<InquiryPopup productUuid={product.uuid} />);
        };

        return (
            <Button size={buttonSize} onClick={openInquiryPopup}>
                {t('Inquire')}
            </Button>
        );
    }

    if (product.isMainVariant) {
        return (
            <ExtendedNextLink className="no-underline" href={product.slug} type="productMainVariant">
                <Button>{t('Choose')}</Button>
            </ExtendedNextLink>
        );
    }

    return (
        <AddToCart
            buttonSize={buttonSize}
            gtmMessageOrigin={gtmMessageOrigin}
            gtmProductListName={gtmProductListName}
            isWithSpinbox={isWithSpinbox}
            listIndex={listIndex}
            maxQuantity={product.stockQuantity}
            minQuantity={1}
            productUuid={product.uuid}
        />
    );
};
