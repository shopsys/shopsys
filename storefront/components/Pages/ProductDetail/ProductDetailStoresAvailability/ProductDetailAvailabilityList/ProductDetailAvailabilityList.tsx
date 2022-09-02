import {
    ProductDetailAvailabilityListItemStatusStyled as AvailabilityListItemStatusStyled,
    ProductDetailAvailabilityListItemStoreLinkStyled as AvailabilityListItemStoreLinkStyled,
    ProductDetailAvailabilityListItemStoreNameStyled as AvailabilityListItemStoreNameStyled,
    ProductDetailAvailabilityListItemStyled as AvailabilityListItemStyled,
    ProductDetailAvailabilityListWrapperStyled as AvailabilityListWrapperStyled,
} from './ProductDetailAvailabilityList.style';
import Heading from 'components/Basic/Heading';
import Icon from 'components/Basic/Icon';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import NextLink from 'next/link';
import { forwardRef } from 'react';
import { StoreAvailabilityType } from 'types/availability';

type ProductDetailAvailabilityListProps = {
    storeAvailabilities: StoreAvailabilityType[];
};

const ProductDetailAvailabilityList = forwardRef<HTMLUListElement, ProductDetailAvailabilityListProps>((props, ref) => {
    const testIdentifier = 'pages-productdetail-availabilitylist-';

    const t = useTypedTranslationFunction();

    return (
        <AvailabilityListWrapperStyled>
            <Heading type="h3">{t('Availability in stores')}</Heading>
            <ul ref={ref}>
                {props.storeAvailabilities.map((storeAvailability, index) => (
                    <AvailabilityListItemStyled key={index} data-testid={testIdentifier + index}>
                        <AvailabilityListItemStoreNameStyled data-testid={testIdentifier + index + '-store'}>
                            {storeAvailability.store.storeName}
                        </AvailabilityListItemStoreNameStyled>
                        <AvailabilityListItemStatusStyled
                            availabilityStatus={storeAvailability.availabilityStatus}
                            data-testid={testIdentifier + index + '-availability'}
                        >
                            {storeAvailability.availabilityInformation}
                        </AvailabilityListItemStatusStyled>
                        <NextLink href={storeAvailability.store.slug} passHref>
                            <AvailabilityListItemStoreLinkStyled data-testid={testIdentifier + index + '-detail'}>
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

ProductDetailAvailabilityList.displayName = 'ProductDetailAvailabilityList';

export default ProductDetailAvailabilityList;
