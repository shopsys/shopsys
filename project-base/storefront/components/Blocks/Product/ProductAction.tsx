import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { AddToCart } from 'components/Blocks/Product/AddToCart';
import { Button } from 'components/Forms/Button/Button';
import { ListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';

type ProductActionProps = {
    product: ListedProductFragment;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    listIndex: number;
};

const wrapperTwClass = 'rounded bg-greyVeryLight p-2';

export const ProductAction: FC<ProductActionProps> = ({ product, gtmProductListName, gtmMessageOrigin, listIndex }) => {
    const { t } = useTranslation();

    if (product.isMainVariant) {
        return (
            <div className={wrapperTwClass}>
                <ExtendedNextLink href={product.slug} type="productMainVariant">
                    <Button className="w-full py-2" name="choose-variant" size="small">
                        {t('Choose variant')}
                    </Button>
                </ExtendedNextLink>
            </div>
        );
    }

    if (product.isSellingDenied) {
        return <div className={twJoin('text-center', wrapperTwClass)}>{t('This item can no longer be purchased')}</div>;
    }

    return (
        <AddToCart
            className={twJoin('w-full', wrapperTwClass)}
            gtmMessageOrigin={gtmMessageOrigin}
            gtmProductListName={gtmProductListName}
            listIndex={listIndex}
            maxQuantity={product.stockQuantity}
            minQuantity={1}
            productUuid={product.uuid}
        />
    );
};
