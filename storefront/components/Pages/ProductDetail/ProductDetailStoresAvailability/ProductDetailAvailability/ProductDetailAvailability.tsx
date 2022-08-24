import {
    ProductDetailAvailabilityInfoStyled as AvailabilityInfoStyled,
    ProductDetailAvailabilityLinkStyled as AvailabilityLinkStyled,
    ProductDetailAvailabilityStyled as AvailabilityStyled,
} from './ProductDetailAvailability.style';
import { Icon } from 'components/Basic/Icon/Icon';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC, RefObject, useEffect } from 'react';
import * as smoothscroll from 'smoothscroll-polyfill';
import { ProductDetailType } from 'types/product';

type ProductDetailAvailabilityProps = {
    product: ProductDetailType;
    scrollTarget: RefObject<HTMLUListElement>;
};

export const ProductDetailAvailability: FC<ProductDetailAvailabilityProps> = (props) => {
    const testIdentifier = 'pages-productdetail-productdetailavailability';

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
        <AvailabilityStyled data-testid={testIdentifier}>
            <AvailabilityLinkStyled status={props.product.availability.status} onClick={scrollOnClickHandler}>
                {props.product.availability.name}
                <Icon iconType="icon" icon="Arrow" />
            </AvailabilityLinkStyled>
            {props.product.availableStoresCount > 0 && (
                <AvailabilityInfoStyled data-testid={testIdentifier + '-availability'}>
                    {t('This item is available immediately in {{ count }} stores', {
                        count: props.product.availableStoresCount,
                    })}
                </AvailabilityInfoStyled>
            )}
            {props.product.exposedStoresCount > 0 && (
                <AvailabilityInfoStyled data-testid={testIdentifier + '-exposed'}>
                    {t('You can check this item in {{ count }} stores', { count: props.product.exposedStoresCount })}
                </AvailabilityInfoStyled>
            )}
        </AvailabilityStyled>
    );
};
