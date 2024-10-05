import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { AddToCart } from 'components/Blocks/Product/AddToCart';
import { Button } from 'components/Forms/Button/Button';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';

type ProductActionProps = {
    product: TypeListedProductFragment;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    listIndex: number;
};

export const ProductAction: FC<ProductActionProps> = ({ product, gtmProductListName, gtmMessageOrigin, listIndex }) => {
    const { t } = useTranslation();

    if (product.isMainVariant) {
        return (
            <ExtendedNextLink className="no-underline" href={product.slug} type="productMainVariant">
                <Button>{t('Choose')}</Button>
            </ExtendedNextLink>
        );
    }

    if (product.isSellingDenied) {
        return <div className="text-center">{t('This item can no longer be purchased')}</div>;
    }

    return (
        <AddToCart
            gtmMessageOrigin={gtmMessageOrigin}
            gtmProductListName={gtmProductListName}
            isWithSpinbox={false}
            listIndex={listIndex}
            maxQuantity={product.stockQuantity}
            minQuantity={1}
            productUuid={product.uuid}
        />
    );
};
