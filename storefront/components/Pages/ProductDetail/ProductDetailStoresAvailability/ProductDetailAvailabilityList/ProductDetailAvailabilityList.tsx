import {
    ProductDetailAvailabilityListItemStatusStyled as AvailabilityListItemStatusStyled,
    ProductDetailAvailabilityListItemStoreLinkStyled as AvailabilityListItemStoreLinkStyled,
    ProductDetailAvailabilityListItemStoreNameStyled as AvailabilityListItemStoreNameStyled,
    ProductDetailAvailabilityListItemStyled as AvailabilityListItemStyled,
    ProductDetailAvailabilityListWrapperStyled as AvailabilityListWrapperStyled,
} from './ProductDetailAvailabilityList.style';
import { forwardRef } from 'react';
import Heading from 'components/Basic/Heading';
import Icon from 'components/Basic/Icon';
import NextLink from 'next/link';
import { StoreAvailability } from 'types/product';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type ProductDetailAvailabilityListProps = {
    storeAvailabilities: StoreAvailability[];
};

const ProductDetailAvailabilityList = forwardRef<HTMLUListElement, ProductDetailAvailabilityListProps>((props, ref) => {
    const t = useTypedTranslationFunction();

    return (
        <AvailabilityListWrapperStyled>
            <Heading type="h3">{t('Availability in stores')}</Heading>
            <ul ref={ref}>
                {props.storeAvailabilities.map((storeAvailability, index) => (
                    <AvailabilityListItemStyled key={index}>
                        <AvailabilityListItemStoreNameStyled>
                            {storeAvailability.storeName}
                        </AvailabilityListItemStoreNameStyled>
                        <AvailabilityListItemStatusStyled availabilityStatus={storeAvailability.availabilityStatus}>
                            {storeAvailability.availabilityInformation}
                        </AvailabilityListItemStatusStyled>
                        <NextLink href="/">
                            <AvailabilityListItemStoreLinkStyled>
                                {t('Store detail')}
                                <Icon iconType="icon" icon="ArrowRight" />
                            </AvailabilityListItemStoreLinkStyled>
                        </NextLink>
                    </AvailabilityListItemStyled>
                ))}
            </ul>
        </AvailabilityListWrapperStyled>
    );
});

/**
 * This is required by TS and the forwardRef function.
 * The display name allows correct displaying in dev-tools.
 */
ProductDetailAvailabilityList.displayName = 'ProductDetailAvailabilityList';

export default ProductDetailAvailabilityList;
