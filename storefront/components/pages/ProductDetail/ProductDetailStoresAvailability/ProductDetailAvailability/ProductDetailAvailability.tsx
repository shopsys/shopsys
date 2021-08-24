import {
    ProductDetailAvailabilityInfoStyled as AvailabilityInfoStyled,
    ProductDetailAvailabilityLinkStyled as AvailabilityLinkStyled,
    ProductDetailAvailabilityStyled as AvailabilityStyled,
} from './ProductDetailAvailability.style';
import { FC, RefObject } from 'react';
import { ProductDetailType } from '../../types';
import ShopsysIcon from 'components/basic/ShopsysIcon';
import { useTranslation } from 'next-i18next';

type ProductDetailAvailabilityProps = {
    product: ProductDetailType;
    scrollTarget: RefObject<HTMLUListElement>;
};

const ProductDetailAvailability: FC<ProductDetailAvailabilityProps> = (props) => {
    const { t } = useTranslation();

    const scrollOnClickHandler = () => {
        if (props.scrollTarget.current !== null) {
            props.scrollTarget.current.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };

    return (
        <AvailabilityStyled>
            <AvailabilityLinkStyled status={props.product.availability.status} onClick={scrollOnClickHandler}>
                {props.product.availability.name}
                <ShopsysIcon icon="arrow" iconHeight={16} iconType="svg" />
            </AvailabilityLinkStyled>
            <AvailabilityInfoStyled>
                {t(
                    '(1)[This item is available immediately in {{ count }} store];(2-inf)[This item is available immediately in {{ count }} stores];',
                    { postProcess: 'interval', count: props.product.availableStoresCount },
                )}
            </AvailabilityInfoStyled>
            <AvailabilityInfoStyled>
                {t(
                    '(1)[You can check this item in {{ count }} store];(2-inf)[You can check this item in {{ count }} stores];',
                    { postProcess: 'interval', count: props.product.exposedStoresCount },
                )}
            </AvailabilityInfoStyled>
        </AvailabilityStyled>
    );
};

export default ProductDetailAvailability;
