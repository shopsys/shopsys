import { Image } from 'components/Basic/Image/Image';
import { ExpectedDeliveryDateInfo } from 'components/Blocks/ExpectedDeliveryDateInfo/ExpectedDeliveryDateInfo';
import {
    TransportAndPaymentPickupPlaceDetail,
    TransportAndPaymentPickupPlaceDetailLayout,
    TransportAndPaymentPickupPlaceOpeningHoursDisplay,
} from 'components/Pages/Order/TransportAndPayment/TransportAndPaymentSelect/TransportAndPaymentPickupPlaceDetail';
import { TIDs } from 'cypress/tids';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { twJoin } from 'tailwind-merge';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

type TransportAndPaymentItemLabelProps = {
    name: string;
    price?: { priceWithVat: string; priceWithoutVat: string; vatAmount: string };
    expectedDeliveryDate?: string | null;
    isPersonalPickup?: boolean;
    description?: string | null;
    image?: TypeImageFragment | null;
    pickupPlaceDetail?: StoreOrPacketeryPoint;
    isActive?: boolean;
    isImageOnWhiteBackground?: boolean;
    disabled?: boolean;
    isPriceNextToDeliveryDateOnSmallScreen?: boolean;
    showChangeButton?: boolean;
    openingHoursDisplay?: TransportAndPaymentPickupPlaceOpeningHoursDisplay;
    pickupPlaceDetailLayout?: TransportAndPaymentPickupPlaceDetailLayout;
    unknownDeliveryDateExplanation?: string;
    openPickupPlacePopup?: () => void;
};

export const TransportAndPaymentItemLabel: FC<TransportAndPaymentItemLabelProps> = ({
    name,
    price,
    expectedDeliveryDate,
    isPersonalPickup = false,
    description,
    image,
    pickupPlaceDetail,
    isActive,
    isImageOnWhiteBackground,
    disabled,
    isPriceNextToDeliveryDateOnSmallScreen = false,
    showChangeButton,
    openingHoursDisplay = 'accordion',
    pickupPlaceDetailLayout = 'default',
    unknownDeliveryDateExplanation,
    openPickupPlacePopup,
}) => {
    const formatPrice = useFormatPrice();

    const imageElement = (
        <div
            data-tid={TIDs.transport_and_payment_list_item_image}
            className={twJoin(
                'isolate flex h-12 w-20 min-w-20 shrink-0 items-center justify-center rounded-xl',
                isImageOnWhiteBackground ? 'bg-background-default' : 'bg-background-more',
                !image && 'hidden',
            )}
        >
            <Image
                alt={image?.name ?? name}
                className="aspect-video h-7 object-contain object-center mix-blend-multiply"
                height={28}
                src={image?.url}
                width={60}
            />
        </div>
    );

    const priceElement = price && isPriceVisible(price.priceWithVat) && (
        <div
            className={twJoin(
                'ml-auto font-secondary font-semibold text-sm text-text-default',
                isPriceNextToDeliveryDateOnSmallScreen &&
                    'max-sm:col-start-3 max-sm:row-start-2 max-sm:whitespace-nowrap',
            )}
        >
            {formatPrice(price.priceWithVat)}
        </div>
    );

    return (
        <div className="flex w-full flex-col">
            <div
                className={twJoin(
                    'items-center',
                    isPriceNextToDeliveryDateOnSmallScreen
                        ? 'grid grid-cols-[auto_1fr_auto] gap-x-3 gap-y-2 sm:flex sm:flex-row sm:gap-3'
                        : 'flex flex-row gap-3',
                )}
            >
                {imageElement}

                <div
                    className={twJoin(
                        'flex min-w-0 flex-col gap-1',
                        isPriceNextToDeliveryDateOnSmallScreen && 'max-sm:contents',
                    )}
                >
                    <div
                        className={twJoin(
                            'flex min-w-0 flex-col gap-1',
                            isPriceNextToDeliveryDateOnSmallScreen &&
                                'max-sm:col-span-2 max-sm:col-start-2 max-sm:row-start-1',
                            isPriceNextToDeliveryDateOnSmallScreen && !image && 'max-sm:col-span-3 max-sm:col-start-1',
                        )}
                    >
                        <div
                            className="text-text-default"
                            data-tid={TIDs.pages_order_selectitem_label_name}
                            id={`${pickupPlaceDetail?.identifier}-${pickupPlaceDetail?.name}`}
                        >
                            {name}
                        </div>

                        {description && <div className="text-text-less text-xs">{description}</div>}
                    </div>

                    {!pickupPlaceDetail && expectedDeliveryDate !== undefined && !disabled && (
                        <div
                            className={twJoin(
                                isPriceNextToDeliveryDateOnSmallScreen && 'max-sm:col-span-2 max-sm:row-start-2',
                            )}
                        >
                            <ExpectedDeliveryDateInfo
                                expectedDeliveryDate={expectedDeliveryDate}
                                isPersonalPickup={isPersonalPickup}
                                unknownDeliveryDateExplanation={unknownDeliveryDateExplanation}
                            />
                        </div>
                    )}
                </div>

                {priceElement}
            </div>

            {pickupPlaceDetail && (
                <TransportAndPaymentPickupPlaceDetail
                    disabled={disabled}
                    expectedDeliveryDate={expectedDeliveryDate}
                    isActive={isActive}
                    layout={pickupPlaceDetailLayout}
                    openingHoursDisplay={openingHoursDisplay}
                    openPickupPlacePopup={openPickupPlacePopup}
                    pickupPlace={pickupPlaceDetail}
                    showChangeButton={showChangeButton}
                />
            )}
        </div>
    );
};
