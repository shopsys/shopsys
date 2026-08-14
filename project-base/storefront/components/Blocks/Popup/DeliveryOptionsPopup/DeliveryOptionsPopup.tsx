import { StoreIcon } from 'components/Basic/Icon/StoreIcon';
import { TruckClockIcon } from 'components/Basic/Icon/TruckClockIcon';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Popup } from 'components/Layout/Popup/Popup';
import { TIDs } from 'cypress/tids';
import { TypeProductDeliveryOptionFragment } from 'graphql/requests/transports/fragments/ProductDeliveryOptionFragment.generated';
import { useProductDeliveryOptionsQuery } from 'graphql/requests/transports/queries/ProductDeliveryOptionsQuery.generated';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible } from 'utils/mappers/price';
import { isPersonalPickupTransport } from 'utils/transport';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { DeliveryOptionsAddressPanel } from './DeliveryOptionsAddressPanel';
import { DeliveryOptionsPanel } from './DeliveryOptionsPanel';
import { DeliveryOptionsPickupPanel } from './DeliveryOptionsPickupPanel';
import { DeliveryOptionsVariantSelect } from './DeliveryOptionsVariantSelect';
import { DeliveryOptionsProduct } from './deliveryOptionsPopupTypes';
import { getDeliveryOptionsContentState } from './deliveryOptionsPopupUtils';

export type DeliveryOptionsPopupProps = {
    products: DeliveryOptionsProduct[];
    preselectedProductUuid?: string;
};

type CollapsiblePanel = 'address' | 'pickup';

const DELIVERY_OPTIONS_POPUP_COMPACT_SCROLL_TARGET_ID = 'delivery-options-popup-compact-scroll';
const DELIVERY_OPTIONS_POPUP_STORES_SCROLL_TARGET_ID = 'delivery-options-popup-stores-scroll';

export const DeliveryOptionsPopup: FC<DeliveryOptionsPopupProps> = ({ products, preselectedProductUuid }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const isDesktop = useMediaMin('lg');
    const [selectedProductUuid, setSelectedProductUuid] = useState<string | null>(
        preselectedProductUuid ?? (products.length === 1 ? products[0].uuid : null),
    );
    const [openCollapsiblePanel, setOpenCollapsiblePanel] = useState<CollapsiblePanel | null>(null);
    const selectedProduct = products.find((product) => product.uuid === selectedProductUuid) ?? null;
    const [{ data: productDeliveryOptionsData, fetching: isFetchingDeliveryOptions, error: deliveryOptionsError }] =
        useProductDeliveryOptionsQuery({
            variables: { productUuid: selectedProduct?.uuid ?? '' },
            pause: selectedProduct === null,
            // the prices and the expected delivery dates change with time, but the query variables
            // (the cache key) stay the same — a cached response could therefore promise outdated data
            requestPolicy: 'network-only',
        });

    const deliveryOptions = productDeliveryOptionsData?.productDeliveryOptions ?? [];
    const pickupDeliveryOptions = deliveryOptions.filter((deliveryOption) =>
        isPersonalPickupTransport(deliveryOption.transport.transportTypeCode),
    );
    const addressDeliveryOptions = deliveryOptions.filter(
        (deliveryOption) => !isPersonalPickupTransport(deliveryOption.transport.transportTypeCode),
    );
    const addressSummary = getDeliveryOptionsPriceSummary(addressDeliveryOptions, formatPrice, t('from'));
    const pickupSummary = getDeliveryOptionsPriceSummary(pickupDeliveryOptions, formatPrice, t('from'));
    const storesScrollableTargetId =
        isDesktop === true
            ? DELIVERY_OPTIONS_POPUP_STORES_SCROLL_TARGET_ID
            : DELIVERY_OPTIONS_POPUP_COMPACT_SCROLL_TARGET_ID;
    const deliveryOptionsContentState = getDeliveryOptionsContentState(
        selectedProduct,
        isFetchingDeliveryOptions,
        deliveryOptionsError,
        productDeliveryOptionsData,
    );

    const renderDeliveryOptionsContent = () => {
        switch (deliveryOptionsContentState.type) {
            case 'no-product':
                return <p className="mt-5">{t('Choose a variant to see the delivery options.')}</p>;
            case 'error':
                return (
                    <p className="mt-5 text-text-error">
                        {t('Delivery options could not be loaded. Please try again later.')}
                    </p>
                );
            case 'empty':
                return <p className="mt-5">{t('No delivery options are available for this product.')}</p>;
            case 'loading':
            case 'loaded':
                return (
                    <div className="mt-4 flex shrink-0 flex-col gap-3 lg:grid lg:min-h-0 lg:flex-1 lg:shrink lg:grid-cols-2 lg:gap-5 lg:overflow-hidden">
                        {/* the columns carry the test ids because their height is bounded by the popup grid,
                            while the inner content can outgrow the popup and fail visibility checks */}
                        <section
                            className={getDeliveryOptionsPanelClassName(openCollapsiblePanel === 'address')}
                            data-tid={TIDs.delivery_options_address_panel}
                        >
                            <DeliveryOptionsPanel
                                icon={<TruckClockIcon className="size-6" />}
                                isDesktop={isDesktop === true}
                                isOpen={openCollapsiblePanel === 'address'}
                                panelId="delivery-options-address-panel-content"
                                summary={addressSummary}
                                title={t('Delivery to address')}
                                onToggle={() =>
                                    setOpenCollapsiblePanel((currentPanel) =>
                                        currentPanel === 'address' ? null : 'address',
                                    )
                                }
                            >
                                {deliveryOptionsContentState.type === 'loading' ? (
                                    <DeliveryOptionsPanelSkeleton />
                                ) : (
                                    <DeliveryOptionsAddressPanel deliveryOptions={addressDeliveryOptions} />
                                )}
                            </DeliveryOptionsPanel>
                        </section>

                        <section
                            className={getDeliveryOptionsPanelClassName(openCollapsiblePanel === 'pickup')}
                            data-tid={TIDs.delivery_options_pickup_panel}
                        >
                            <DeliveryOptionsPanel
                                icon={<StoreIcon className="size-6" />}
                                isDesktop={isDesktop === true}
                                isOpen={openCollapsiblePanel === 'pickup'}
                                panelId="delivery-options-pickup-panel-content"
                                scrollableContentId={
                                    isDesktop === true ? DELIVERY_OPTIONS_POPUP_STORES_SCROLL_TARGET_ID : undefined
                                }
                                summary={pickupSummary}
                                title={t('Pickup at a store')}
                                onToggle={() =>
                                    setOpenCollapsiblePanel((currentPanel) =>
                                        currentPanel === 'pickup' ? null : 'pickup',
                                    )
                                }
                            >
                                {deliveryOptionsContentState.type === 'loading' ? (
                                    <DeliveryOptionsPanelSkeleton />
                                ) : (
                                    <DeliveryOptionsPickupPanel
                                        pickupDeliveryOptions={pickupDeliveryOptions}
                                        product={deliveryOptionsContentState.selectedProduct}
                                        scrollableTargetId={storesScrollableTargetId}
                                    />
                                )}
                            </DeliveryOptionsPanel>
                        </section>
                    </div>
                );
        }
    };

    return (
        <Popup
            className="h-[min(880px,95dvh)] max-h-[95dvh] w-11/12 max-w-6xl"
            contentClassName="flex min-h-0 flex-1 flex-col overflow-hidden"
            title={t('Delivery options')}
        >
            <div
                className="flex min-h-0 flex-1 flex-col overflow-y-auto lg:overflow-hidden"
                data-tid={TIDs.product_detail_delivery_options_popup}
                id={DELIVERY_OPTIONS_POPUP_COMPACT_SCROLL_TARGET_ID}
            >
                <p className="text-sm text-text-less">
                    {t(
                        'Valid when buying this product alone, the final options may differ based on the cart contents.',
                    )}
                </p>

                {products.length > 1 && (
                    <div className="mt-3">
                        <DeliveryOptionsVariantSelect
                            products={products}
                            selectedProduct={selectedProduct}
                            onSelectProduct={setSelectedProductUuid}
                        />
                    </div>
                )}

                {renderDeliveryOptionsContent()}
            </div>
        </Popup>
    );
};

const DeliveryOptionsPanelSkeleton: FC = () => (
    <div className="flex flex-col gap-2.5">
        <Skeleton className="h-16 w-full" />
        <Skeleton className="h-16 w-full" />
        <Skeleton className="h-16 w-full" />
    </div>
);

const getDeliveryOptionsPanelClassName = (isOpen: boolean): string =>
    twJoin(
        'flex min-h-0 flex-col overflow-hidden max-lg:rounded-xl max-lg:border max-lg:transition',
        isOpen
            ? 'max-lg:border-border-less max-lg:bg-background-default'
            : 'max-lg:border-transparent max-lg:bg-background-more max-lg:hover:border-border-less max-lg:hover:bg-background-default',
    );

type FormatPrice = ReturnType<typeof useFormatPrice>;

const getDeliveryOptionsPriceSummary = (
    deliveryOptions: TypeProductDeliveryOptionFragment[],
    formatPrice: FormatPrice,
    fromLabel: string,
): string | null => {
    const visiblePrices = deliveryOptions
        .map((deliveryOption) => deliveryOption.price.priceWithVat)
        .filter(isPriceVisible)
        .map(Number.parseFloat);

    if (visiblePrices.length === 0) {
        return null;
    }

    const minimumPrice = Math.min(...visiblePrices);
    const hasDifferentPrices = new Set(visiblePrices).size > 1;

    return `${hasDifferentPrices ? `${fromLabel} ` : ''}${formatPrice(minimumPrice, {
        explicitZero: hasDifferentPrices,
    })}`;
};
