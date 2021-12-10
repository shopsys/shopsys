import {
    AvailabilityPopupStyled,
    VariantActionCellStyled,
    VariantActionStyled,
    VariantAvailabilityCellStyled,
    VariantCellStyled,
    VariantImageCellStyled,
    VariantImageWrapperStyled,
    VariantPriceCellStyled,
} from './Variant.style';
import { FC, useState } from 'react';
import AddToCart from 'components/Blocks/Product/AddToCart/AddToCart';
import { formatPrice } from 'utils/formatting';
import Image from 'components/Basic/Image';
import { ListedVariantType } from 'connectors/products/types';
import Popup from 'components/Layout/Popup';
import ProductAvailableStoresCount from 'components/Blocks/Product/Availability/ProductAvailableStoresCount';
import ProductDetailAvailabilityList from 'components/Pages/ProductDetail/ProductDetailStoresAvailability/ProductDetailAvailabilityList';
import ProductExposedStoresCount from 'components/Blocks/Product/Availability/ProductExposedStoresCount';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { VariantsTableRowStyled } from 'components/Pages/ProductDetail/ProductVariantsTable/ProductVariantsTable.style';

type VariantProps = {
    variant: ListedVariantType;
};

const Variant: FC<VariantProps> = (props) => {
    const t = useTypedTranslationFunction();
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const [isAvailabilityPopupVisible, setAvailabilityPopupVisibility] = useState(false);

    return (
        <>
            <VariantsTableRowStyled key={props.variant.uuid}>
                <VariantImageCellStyled>
                    <VariantImageWrapperStyled>
                        <Image alt={props.variant.name} image={props.variant.images[0]?.galleryThumbnail} />
                    </VariantImageWrapperStyled>
                </VariantImageCellStyled>
                <VariantCellStyled>{props.variant.name}</VariantCellStyled>
                <VariantAvailabilityCellStyled onClick={() => setAvailabilityPopupVisibility(true)}>
                    {props.variant.availability}
                    <ProductAvailableStoresCount
                        isMainVariant={false}
                        availableStoresCount={props.variant.availableStoresCount}
                    />
                    <ProductExposedStoresCount
                        isMainVariant={false}
                        exposedStoresCount={props.variant.exposedStoresCount}
                    />
                </VariantAvailabilityCellStyled>
                <VariantPriceCellStyled>
                    {formatPrice(props.variant.price.priceWithVat, currencyCode, t)}
                </VariantPriceCellStyled>
                <VariantActionCellStyled>
                    <VariantActionStyled>
                        <AddToCart
                            productUuid={props.variant.uuid}
                            productName={props.variant.name}
                            minQuantity={1}
                            maxQuantity={props.variant.stockQuantity}
                        />
                    </VariantActionStyled>
                </VariantActionCellStyled>
            </VariantsTableRowStyled>
            {isAvailabilityPopupVisible && (
                <Popup
                    isVisible={isAvailabilityPopupVisible}
                    onCloseCallback={() => setAvailabilityPopupVisibility(false)}
                    wrapperComponent={AvailabilityPopupStyled}
                >
                    <ProductDetailAvailabilityList storeAvailabilities={props.variant.storeAvailabilities} />
                </Popup>
            )}
        </>
    );
};

export default Variant;
