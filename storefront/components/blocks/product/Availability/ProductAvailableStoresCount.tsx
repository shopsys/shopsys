import { FC } from 'react';
import { ProductItemType } from '../types';
import { useTranslation } from 'react-i18next';

const ProductAvailableStoresCount: FC<ProductItemType> = (props) => {
    const { t } = useTranslation();

    if (props.isMainVariant || props.availableStoresCount === 0) {
        return null;
    }

    return (
        <div>
            {t(
                '(1)[This item is available immediately in {{ count }} store];(2-inf)[This item is available immediately in {{ count }} stores];',
                { postProcess: 'interval', count: props.availableStoresCount },
            )}
        </div>
    );
};

export default ProductAvailableStoresCount;
