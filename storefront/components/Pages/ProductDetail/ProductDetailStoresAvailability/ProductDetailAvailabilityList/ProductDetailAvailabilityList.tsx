import {
    ProductDetailAvailabilityListItemStatusStyled as AvailabilityListItemStatusStyled,
    ProductDetailAvailabilityListItemStoreLinkStyled as AvailabilityListItemStoreLinkStyled,
    ProductDetailAvailabilityListItemStoreNameStyled as AvailabilityListItemStoreNameStyled,
    ProductDetailAvailabilityListItemStyled as AvailabilityListItemStyled,
    ProductDetailAvailabilityListWrapperStyled as AvailabilityListWrapperStyled,
} from './ProductDetailAvailabilityList.style';
import { forwardRef } from 'react';
import Icon from 'components/Basic/Icon';
import Link from 'next/link';
import { ProductDetailType } from '../../types';
import ShopsysHeading from 'components/Basic/ShopsysHeading';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

type ProductDetailAvailabilityListProps = {
    product: ProductDetailType;
};

const ProductDetailAvailabilityList = forwardRef<HTMLUListElement, ProductDetailAvailabilityListProps>((props, ref) => {
    const t = useTypedTranslationFunction();

    return (
        <AvailabilityListWrapperStyled>
            <ShopsysHeading type="h3">{t<string>('Availability in stores')}</ShopsysHeading>
            <ul ref={ref}>
                {props.product.storeAvailabilities.map((storeAvailability, index) => (
                    <AvailabilityListItemStyled key={index}>
                        <AvailabilityListItemStoreNameStyled>
                            {storeAvailability.storeName}
                        </AvailabilityListItemStoreNameStyled>
                        <AvailabilityListItemStatusStyled availabilityStatus={storeAvailability.availabilityStatus}>
                            {storeAvailability.availabilityInformation}
                        </AvailabilityListItemStatusStyled>
                        <Link href="/">
                            <AvailabilityListItemStoreLinkStyled>
                                {t('Store detail')}
                                <Icon icon="ArrowRight" iconHeight={16} />
                            </AvailabilityListItemStoreLinkStyled>
                        </Link>
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
