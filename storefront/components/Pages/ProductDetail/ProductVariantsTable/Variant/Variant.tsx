import {
    VariantActionCellStyled,
    VariantActionStyled,
    VariantCellStyled,
    VariantImageCellStyled,
    VariantImageWrapperStyled,
    VariantPriceCellStyled,
} from './Variant.style';
import { FC } from 'react';
import { formatPrice } from 'utils/formatting';
import Image from 'components/Basic/Image';
import { ListedVariantType } from 'connectors/products/types';
import ProductDetailAvailabilityList from 'components/Pages/ProductDetail/ProductDetailStoresAvailability/ProductDetailAvailabilityList';
import { VariantsTableRowStyled } from 'components/Pages/ProductDetail/ProductVariantsTable/ProductVariantsTable.style';

type VariantProps = {
    variant: ListedVariantType;
};

const Variant: FC<VariantProps> = (props) => {
    return (
        <>
            <VariantsTableRowStyled key={props.variant.uuid}>
                <VariantImageCellStyled>
                    <VariantImageWrapperStyled>
                        <Image alt={props.variant.name} image={props.variant.image} />
                    </VariantImageWrapperStyled>
                </VariantImageCellStyled>
                <VariantCellStyled>{props.variant.name}</VariantCellStyled>
                <VariantCellStyled onClick={() => setAvailabilityPopupVisibility(true)}>
                    {props.variant.availability}
                </VariantCellStyled>
                <VariantPriceCellStyled>
                    {formatPrice(props.variant.price.priceWithVat, currencyCode, t)}
                </VariantPriceCellStyled>
                <VariantActionCellStyled>
                    <VariantActionStyled></VariantActionStyled>
                </VariantActionCellStyled>
            </VariantsTableRowStyled>
        </>
    );
};

export default Variant;
