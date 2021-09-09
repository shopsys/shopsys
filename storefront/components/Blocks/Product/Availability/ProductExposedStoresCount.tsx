import { FC } from 'react';
import { ProductExposedStoreCountStyled } from './ProductExposedStoresCount.style';
import { SliderProductItemType } from '../types';
import { useTranslation } from 'react-i18next';

const ProductExposedStoresCount: FC<SliderProductItemType> = (props) => {
    const { t } = useTranslation();

    if (props.isMainVariant || props.exposedStoresCount === 0) {
        return null;
    }

    return (
        <ProductExposedStoreCountStyled>
            {t(
                '(1)[You can check this item in {{ count }} store];(2-inf)[You can check this item in {{ count }} stores];',
                { postProcess: 'interval', count: props.exposedStoresCount },
            )}
        </ProductExposedStoreCountStyled>
    );
};

export default ProductExposedStoresCount;
