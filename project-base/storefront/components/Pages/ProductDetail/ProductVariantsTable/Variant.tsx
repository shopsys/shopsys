import { ProductVariantsTableRow } from './ProductVariantsTableRow';
import { Image } from 'components/Basic/Image/Image';
import { AddToCart } from 'components/Blocks/Product/AddToCart';
import { ProductAvailableStoresCount } from 'components/Blocks/Product/ProductAvailableStoresCount';
import { ProductDetailAvailabilityList } from 'components/Pages/ProductDetail/ProductDetailAvailabilityList';
import { MainVariantDetailFragmentApi } from 'graphql/generated';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { twMergeCustom } from 'helpers/twMerge';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useState } from 'react';

const Popup = dynamic(() => import('components/Layout/Popup/Popup').then((component) => component.Popup));

type VariantProps = {
    variant: MainVariantDetailFragmentApi['variants'][number];
    isSellingDenied: boolean;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    listIndex: number;
};

const TEST_IDENTIFIER = 'pages-productdetail-variant-';

export const Variant: FC<VariantProps> = ({
    gtmProductListName,
    gtmMessageOrigin,
    isSellingDenied,
    listIndex,
    variant,
}) => {
    const formatPrice = useFormatPrice();
    const [isAvailabilityPopupVisible, setAvailabilityPopupVisibility] = useState(false);
    const { t } = useTranslation();

    return (
        <>
            <ProductVariantsTableRow key={variant.uuid} dataTestId={TEST_IDENTIFIER + variant.catalogNumber}>
                <Cell className="float-left w-10 pl-0 lg:float-none">
                    <div className="w-20 pr-2">
                        <Image
                            alt={variant.mainImage?.name || variant.fullName}
                            image={variant.mainImage}
                            type="default"
                        />
                    </div>
                </Cell>
                <Cell dataTestId={TEST_IDENTIFIER + 'name'}>{variant.fullName}</Cell>
                <Cell
                    className="cursor-pointer"
                    dataTestId={TEST_IDENTIFIER + 'availability'}
                    onClick={() => setAvailabilityPopupVisibility(true)}
                >
                    {variant.availability.name}
                    <ProductAvailableStoresCount
                        availableStoresCount={variant.availableStoresCount}
                        isMainVariant={false}
                    />
                </Cell>
                <Cell className="lg:text-right" dataTestId={TEST_IDENTIFIER + 'price'}>
                    {formatPrice(variant.price.priceWithVat)}
                </Cell>
                <Cell className="text-right max-lg:clear-both max-lg:pl-0 lg:w-60">
                    {isSellingDenied ? (
                        <>{t('This item can no longer be purchased')}</>
                    ) : (
                        <AddToCart
                            gtmMessageOrigin={gtmMessageOrigin}
                            gtmProductListName={gtmProductListName}
                            listIndex={listIndex}
                            maxQuantity={variant.stockQuantity}
                            minQuantity={1}
                            productUuid={variant.uuid}
                        />
                    )}
                </Cell>
            </ProductVariantsTableRow>
            {isAvailabilityPopupVisible && (
                <Popup className="w-11/12 max-w-2xl" onCloseCallback={() => setAvailabilityPopupVisibility(false)}>
                    <ProductDetailAvailabilityList storeAvailabilities={variant.storeAvailabilities} />
                </Popup>
            )}
        </>
    );
};

type CellProps = { onClick?: () => void };

const Cell: FC<CellProps> = ({ className, children, dataTestId, onClick }) => (
    <td
        data-testid={dataTestId}
        className={twMergeCustom(
            'block pl-20 text-left align-middle text-xs lg:table-cell lg:border-b lg:border-greyLighter lg:px-1 lg:py-2',
            className,
        )}
        onClick={onClick}
    >
        {children}
    </td>
);
