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
import { Image } from 'components/Basic/Image/Image';
import { AddToCart } from 'components/Blocks/Product/AddToCart/AddToCart';
import { ProductAvailableStoresCount } from 'components/Blocks/Product/Availability/ProductAvailableStoresCount';
import { ProductExposedStoresCount } from 'components/Blocks/Product/Availability/ProductExposedStoresCount';
import { Popup } from 'components/Layout/Popup/Popup';
import { ProductDetailAvailabilityList } from 'components/Pages/ProductDetail/ProductDetailStoresAvailability/ProductDetailAvailabilityList/ProductDetailAvailabilityList';
import { VariantsTableRowStyled } from 'components/Pages/ProductDetail/ProductVariantsTable/ProductVariantsTable.style';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC, useState } from 'react';
import { GtmListNameType } from 'types/gtm';
import { ListedVariantType } from 'types/product';

type VariantProps = {
    variant: ListedVariantType;
    isSellingDenied: boolean;
    gtmListName: GtmListNameType;
    listIndex: number;
};

const TEST_IDENTIFIER = 'pages-productdetail-variant-';

export const Variant: FC<VariantProps> = ({ gtmListName, isSellingDenied, listIndex, variant }) => {
    const formatPrice = useFormatPrice();
    const [isAvailabilityPopupVisible, setAvailabilityPopupVisibility] = useState(false);
    const t = useTypedTranslationFunction();

    return (
        <>
            <VariantsTableRowStyled key={variant.uuid} data-testid={TEST_IDENTIFIER + variant.catalogNumber}>
                <VariantImageCellStyled>
                    <VariantImageWrapperStyled>
                        <Image alt={variant.fullName} type="default" image={variant.image} />
                    </VariantImageWrapperStyled>
                </VariantImageCellStyled>
                <VariantCellStyled data-testid={TEST_IDENTIFIER + 'name'}>{variant.fullName}</VariantCellStyled>
                <VariantAvailabilityCellStyled
                    onClick={() => setAvailabilityPopupVisibility(true)}
                    data-testid={TEST_IDENTIFIER + 'availability'}
                >
                    {variant.availability.name}
                    <ProductAvailableStoresCount
                        isMainVariant={false}
                        availableStoresCount={variant.availableStoresCount}
                    />
                    <ProductExposedStoresCount isMainVariant={false} exposedStoresCount={variant.exposedStoresCount} />
                </VariantAvailabilityCellStyled>
                <VariantPriceCellStyled data-testid={TEST_IDENTIFIER + 'price'}>
                    {formatPrice(variant.price.priceWithVat)}
                </VariantPriceCellStyled>
                <VariantActionCellStyled>
                    {isSellingDenied ? (
                        <>{t('This item can no longer be purchased')}</>
                    ) : (
                        <VariantActionStyled>
                            <AddToCart
                                productUuid={variant.uuid}
                                minQuantity={1}
                                maxQuantity={variant.stockQuantity}
                                gtmListName={gtmListName}
                                listIndex={listIndex}
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
                    <ProductDetailAvailabilityList storeAvailabilities={variant.storeAvailabilities} />
                </Popup>
            )}
        </>
    );
};
