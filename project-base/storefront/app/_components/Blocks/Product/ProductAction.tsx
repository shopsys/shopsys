'use client';

import { AddToCart } from 'app/_components/Blocks/Product/AddToCart';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Button } from 'components/Forms/Button/Button';
import { useTranslation } from 'components/providers/TranslationProvider';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';

const InquiryPopup = dynamic(
    () => import('app/_components/Blocks/Popup/InquiryPopup').then((component) => component.InquiryPopup),
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
    buttonVariant?: 'primary' | 'inverted';
};

export const ProductAction: FC<ProductActionProps> = ({
    product,
    gtmProductListName,
    gtmMessageOrigin,
    listIndex,
    isWithSpinbox = false,
    buttonSize,
    buttonVariant = 'primary',
}) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    if (product.isSellingDenied) {
        return <div className="max-w-[215px] text-center">{t('This item can no longer be purchased')}</div>;
    }

    if (!product.isMainVariant && product.isInquiryType) {
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
            buttonVariant={buttonVariant}
            gtmMessageOrigin={gtmMessageOrigin}
            gtmProductListName={gtmProductListName}
            isWithSpinbox={isWithSpinbox}
            listIndex={listIndex}
            minQuantity={1}
            productUuid={product.uuid}
        />
    );
};
