import * as smoothscroll from 'smoothscroll-polyfill';
import {
    ProductDetailAvailabilityInfoStyled as AvailabilityInfoStyled,
    ProductDetailAvailabilityLinkStyled as AvailabilityLinkStyled,
    ProductDetailAvailabilityStyled as AvailabilityStyled,
} from './ProductDetailAvailability.style';
import { FC, RefObject, useEffect } from 'react';
import Icon from 'components/Basic/Icon';
import { ProductDetailType } from '../../types';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type ProductDetailAvailabilityProps = {
    product: ProductDetailType;
    scrollTarget: RefObject<HTMLUListElement>;
};

const ProductDetailAvailability: FC<ProductDetailAvailabilityProps> = (props) => {
    const t = useTypedTranslationFunction();

    const scrollOnClickHandler = () => {
        if (props.scrollTarget.current !== null) {
            props.scrollTarget.current.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };

    useEffect(() => {
        smoothscroll.polyfill();
    }, []);

    return (
        <AvailabilityStyled>
            <AvailabilityLinkStyled status={props.product.availability.status} onClick={scrollOnClickHandler}>
                {props.product.availability.name}
                <Icon icon="Arrow" iconHeight={16} />
            </AvailabilityLinkStyled>
            {props.product.availableStoresCount > 0 && (
                <AvailabilityInfoStyled>
                    {t(
                        '(1)[This item is available immediately in {{ count }} store];(2-inf)[This item is available immediately in {{ count }} stores];',
                        { postProcess: 'interval', count: props.product.availableStoresCount },
                    )}
                </AvailabilityInfoStyled>
            )}
            {props.product.exposedStoresCount > 0 && (
                <AvailabilityInfoStyled>
                    {t(
                        '(1)[You can check this item in {{ count }} store];(2-inf)[You can check this item in {{ count }} stores];',
                        { postProcess: 'interval', count: props.product.exposedStoresCount },
                    )}
                </AvailabilityInfoStyled>
            )}
        </AvailabilityStyled>
    );
};

export default ProductDetailAvailability;
