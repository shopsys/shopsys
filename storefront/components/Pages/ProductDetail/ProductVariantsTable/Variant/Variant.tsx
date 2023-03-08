import { ProductVariantsTableRow } from '../ProductVariantsTableRow';
import { Image } from 'components/Basic/Image/Image';
import { AddToCart } from 'components/Blocks/Product/AddToCart/AddToCart';
import { ProductAvailableStoresCount } from 'components/Blocks/Product/Availability/ProductAvailableStoresCount';
import { ProductExposedStoresCount } from 'components/Blocks/Product/Availability/ProductExposedStoresCount';
import { Popup } from 'components/Layout/Popup/Popup';
import { ProductDetailAvailabilityList } from 'components/Pages/ProductDetail/ProductDetailStoresAvailability/ProductDetailAvailabilityList/ProductDetailAvailabilityList';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useState } from 'react';
import { GtmListNameType } from 'types/gtm';
import { ListedVariantType } from 'types/product';
import { twMergeCustom } from 'utils/twMerge';

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
            <ProductVariantsTableRow key={variant.uuid} dataTestId={TEST_IDENTIFIER + variant.catalogNumber}>
                <Cell className="max-lg:float-left max-lg:w-10 max-lg:pl-0 lg:w-24">
                    <div className="h-16 w-16">
                        <Image alt={variant.fullName} type="default" image={variant.image} />
                    </div>
                </Cell>
                <Cell dataTestId={TEST_IDENTIFIER + 'name'}>{variant.fullName}</Cell>
                <Cell
                    className="cursor-pointer"
                    onClick={() => setAvailabilityPopupVisibility(true)}
                    dataTestId={TEST_IDENTIFIER + 'availability'}
                >
                    {variant.availability.name}
                    <ProductAvailableStoresCount
                        isMainVariant={false}
                        availableStoresCount={variant.availableStoresCount}
                    />
                    <ProductExposedStoresCount isMainVariant={false} exposedStoresCount={variant.exposedStoresCount} />
                </Cell>
                <Cell className="lg:text-right" dataTestId={TEST_IDENTIFIER + 'price'}>
                    {formatPrice(variant.price.priceWithVat)}
                </Cell>
                <Cell className="text-right max-lg:clear-both max-lg:pl-0 lg:w-60">
                    {isSellingDenied ? (
                        <>{t('This item can no longer be purchased')}</>
                    ) : (
                        <div className="flex justify-between lg:justify-around ">
                            <AddToCart
                                productUuid={variant.uuid}
                                minQuantity={1}
                                maxQuantity={variant.stockQuantity}
                                gtmListName={gtmListName}
                                listIndex={listIndex}
                            />
                        </div>
                    )}
                </Cell>
            </ProductVariantsTableRow>
            {isAvailabilityPopupVisible && (
                <Popup
                    isVisible={isAvailabilityPopupVisible}
                    onCloseCallback={() => setAvailabilityPopupVisibility(false)}
                    className="w-11/12 max-w-2xl"
                >
                    <ProductDetailAvailabilityList storeAvailabilities={variant.storeAvailabilities} />
                </Popup>
            )}
        </>
    );
};

type CellProps = { onClick?: () => void };

const Cell: FC<CellProps> = ({ className, children, dataTestId, onClick }) => (
    <td
        className={twMergeCustom(
            'block pl-14 text-left align-middle text-xs lg:table-cell lg:border-b lg:border-greyLighter lg:p-1',
            className,
        )}
        data-testid={dataTestId}
        onClick={onClick}
    >
        {children}
    </td>
);
