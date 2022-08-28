import {
    ProductDetailAvailabilityListItemStatusStyled as AvailabilityListItemStatusStyled,
    ProductDetailAvailabilityListItemStoreLinkStyled as AvailabilityListItemStoreLinkStyled,
    ProductDetailAvailabilityListItemStoreNameStyled as AvailabilityListItemStoreNameStyled,
    ProductDetailAvailabilityListItemStyled as AvailabilityListItemStyled,
    ProductDetailAvailabilityListWrapperStyled as AvailabilityListWrapperStyled,
} from './ProductDetailAvailabilityList.style';
import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import NextLink from 'next/link';
import { forwardRef } from 'react';
import { StoreAvailabilityType } from 'types/availability';

type ProductDetailAvailabilityListProps = {
    storeAvailabilities: StoreAvailabilityType[];
};

const TEST_IDENTIFIER = 'pages-productdetail-availabilitylist-';

export const ProductDetailAvailabilityList = forwardRef<HTMLUListElement, ProductDetailAvailabilityListProps>(
    ({ storeAvailabilities }, ref) => {
        const t = useTypedTranslationFunction();

        return (
            <AvailabilityListWrapperStyled>
                <Heading type="h3">{t('Availability in stores')}</Heading>
                <ul ref={ref}>
                    {storeAvailabilities.map((storeAvailability, index) => (
                        <AvailabilityListItemStyled key={index} data-testid={TEST_IDENTIFIER + index}>
                            <AvailabilityListItemStoreNameStyled data-testid={TEST_IDENTIFIER + index + '-store'}>
                                {storeAvailability.store.storeName}
                            </AvailabilityListItemStoreNameStyled>
                            <AvailabilityListItemStatusStyled
                                availabilityStatus={storeAvailability.availabilityStatus}
                                data-testid={TEST_IDENTIFIER + index + '-availability'}
                            >
                                {storeAvailability.availabilityInformation}
                            </AvailabilityListItemStatusStyled>
                            <NextLink href={storeAvailability.store.slug} passHref>
                                <AvailabilityListItemStoreLinkStyled data-testid={TEST_IDENTIFIER + index + '-detail'}>
                                    {t('Store detail')}
                                    <Icon iconType="icon" icon="ArrowRight" />
                                </AvailabilityListItemStoreLinkStyled>
                            </NextLink>
                        </AvailabilityListItemStyled>
                    ))}
                </ul>
            </AvailabilityListWrapperStyled>
        );
    },
);

ProductDetailAvailabilityList.displayName = 'ProductDetailAvailabilityList';
