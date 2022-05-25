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
import Image from 'components/Basic/Image';
import AddToCart from 'components/Blocks/Product/AddToCart/AddToCart';
import ProductAvailableStoresCount from 'components/Blocks/Product/Availability/ProductAvailableStoresCount';
import ProductExposedStoresCount from 'components/Blocks/Product/Availability/ProductExposedStoresCount';
import Popup from 'components/Layout/Popup';
import ProductDetailAvailabilityList from 'components/Pages/ProductDetail/ProductDetailStoresAvailability/ProductDetailAvailabilityList';
import { VariantsTableRowStyled } from 'components/Pages/ProductDetail/ProductVariantsTable/ProductVariantsTable.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC, useState } from 'react';
import { useShopsysSelector } from 'redux/main';
import { ListedVariantType } from 'types/product';
import { formatPrice } from 'utils/formatting';

type VariantProps = {
    variant: ListedVariantType;
    isSellingDenied: boolean;
};

const Variant: FC<VariantProps> = (props) => {
    const testIdentifier = 'pages-productdetail-variant-';

    const t = useTypedTranslationFunction();
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const [isAvailabilityPopupVisible, setAvailabilityPopupVisibility] = useState(false);

    return (
        <>
            <VariantsTableRowStyled key={props.variant.uuid} data-testid={testIdentifier + props.variant.catalogNumber}>
                <VariantImageCellStyled>
                    <VariantImageWrapperStyled>
                        <Image alt={props.variant.name} type="default" image={props.variant.image} />
                    </VariantImageWrapperStyled>
                </VariantImageCellStyled>
                <VariantCellStyled data-testid={testIdentifier + 'name'}>{props.variant.name}</VariantCellStyled>
                <VariantAvailabilityCellStyled
                    onClick={() => setAvailabilityPopupVisibility(true)}
                    data-testid={testIdentifier + 'availability'}
                >
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
                <VariantPriceCellStyled data-testid={testIdentifier + 'price'}>
                    {formatPrice(props.variant.price.priceWithVat, currencyCode, t)}
                </VariantPriceCellStyled>
                <VariantActionCellStyled>
                    {props.isSellingDenied ? (
                        <>{t('This item can no longer be purchased')}</>
                    ) : (
                        <VariantActionStyled>
                            <AddToCart
                                productUuid={props.variant.uuid}
                                productName={props.variant.name}
                                minQuantity={1}
                                maxQuantity={props.variant.stockQuantity}
                            />
                        </VariantActionStyled>
                    )}
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
