import { FC } from 'react';
import { ProductItemType } from '../types';
import { useTranslation } from 'react-i18next';

const ProductExposedStoresCount: FC<ProductItemType> = (props) => {
    const { t } = useTranslation();

    if (props.isMainVariant || props.exposedStoresCount === 0) {
        return null;
    }

    return (
        <div>
            {t(
                '(1)[You can check this item in {{ count }} store];(2-inf)[You can check this item in {{ count }} stores];',
                { postProcess: 'interval', count: props.exposedStoresCount },
            )}
        </div>
    );
};

export default ProductExposedStoresCount;
