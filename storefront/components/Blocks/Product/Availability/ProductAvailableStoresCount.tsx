import { FC } from 'react';
import { ProductAvailableStoreCountStyled } from './ProductAvailableStoresCount.style';
import { SliderProductItemType } from '../types';
import { useTranslation } from 'react-i18next';

const ProductAvailableStoresCount: FC<SliderProductItemType> = (props) => {
    const { t } = useTranslation();

    if (props.isMainVariant || props.availableStoresCount === 0) {
        return null;
    }

    return (
        <ProductAvailableStoreCountStyled>
            {t(
                '(1)[This item is available immediately in {{ count }} store];(2-inf)[This item is available immediately in {{ count }} stores];',
                { postProcess: 'interval', count: props.availableStoresCount },
            )}
        </ProductAvailableStoreCountStyled>
    );
};

export default ProductAvailableStoresCount;
