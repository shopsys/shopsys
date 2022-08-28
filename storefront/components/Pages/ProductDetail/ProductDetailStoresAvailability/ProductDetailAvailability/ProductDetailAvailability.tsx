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

const TEST_IDENTIFIER = 'pages-productdetail-productdetailavailability';

export const ProductDetailAvailability: FC<ProductDetailAvailabilityProps> = ({ product, scrollTarget }) => {
    const t = useTypedTranslationFunction();

    const scrollOnClickHandler = () => {
        if (scrollTarget.current !== null) {
            scrollTarget.current.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };

    useEffect(() => {
        smoothscroll.polyfill();
    }, []);

    return (
        <AvailabilityStyled data-testid={TEST_IDENTIFIER}>
            <AvailabilityLinkStyled availabilityStatus={product.availability.status} onClick={scrollOnClickHandler}>
                {product.availability.name}
                <Icon iconType="icon" icon="Arrow" />
            </AvailabilityLinkStyled>
            {product.availableStoresCount > 0 && (
                <AvailabilityInfoStyled data-testid={TEST_IDENTIFIER + '-availability'}>
                    {t('This item is available immediately in {{ count }} stores', {
                        count: product.availableStoresCount,
                    })}
                </AvailabilityInfoStyled>
            )}
            {product.exposedStoresCount > 0 && (
                <AvailabilityInfoStyled data-testid={TEST_IDENTIFIER + '-exposed'}>
                    {t('You can check this item in {{ count }} stores', { count: product.exposedStoresCount })}
                </AvailabilityInfoStyled>
            )}
        </AvailabilityStyled>
    );
};
