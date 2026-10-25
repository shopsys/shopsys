import { Alert } from 'components/Basic/Alert/Alert';
import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { WarningIcon } from 'components/Basic/Icon/WarningIcon';
import { TIDs } from 'cypress/tids';
import { useProductVariantsQuery } from 'graphql/requests/products/queries/ProductVariantsQuery.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'utils/i18n/useTranslationWrapper';

import { ProductVariantsPopupRow } from './ProductVariantsPopupRow';
import { ProductVariantsPopupSkeleton } from './ProductVariantsPopupSkeleton';

export type ProductVariantsPopupContentProps = {
    productUuid: string;
    gtmMessageOrigin: GtmMessageOriginType;
    gtmProductListName: GtmProductListNameType;
    listIndex: number;
};

export const ProductVariantsPopupContent: FC<ProductVariantsPopupContentProps> = ({
    productUuid,
    gtmMessageOrigin,
    gtmProductListName,
    listIndex,
}) => {
    const { t } = useTranslation();
    const [{ data, fetching, error }] = useProductVariantsQuery({
        variables: { uuid: productUuid },
        // Refresh availability and customer prices each time the selector opens.
        requestPolicy: 'network-only',
    });
    const product = data?.product;

    if (fetching) {
        return <ProductVariantsPopupSkeleton />;
    }

    if (error || product?.__typename !== 'MainVariant') {
        return (
            <div role="alert">
                <Alert icon={WarningIcon} variant="error">
                    {t('Product variants could not be loaded. Please try again later.')}
                </Alert>
            </div>
        );
    }

    if (product.variants.length === 0) {
        return (
            <Alert icon={InfoIcon} variant="info">
                {t('Currently, it is not possible to purchase any variant of this product.')}
            </Alert>
        );
    }

    return (
        <ul className="flex flex-col gap-3" data-tid={TIDs.product_variants_popup_list}>
            {product.variants.map((variant) => (
                <ProductVariantsPopupRow
                    gtmMessageOrigin={gtmMessageOrigin}
                    gtmProductListName={gtmProductListName}
                    key={variant.uuid}
                    listIndex={listIndex}
                    variant={variant}
                />
            ))}
        </ul>
    );
};
