import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { Image } from 'components/Basic/Image/Image';
import {
    TransportAndPaymentPickupPlaceDetail,
    TransportAndPaymentPickupPlaceDetailLayout,
    TransportAndPaymentPickupPlaceOpeningHoursDisplay,
} from 'components/Pages/Order/TransportAndPayment/TransportAndPaymentSelect/TransportAndPaymentPickupPlaceDetail';
import { useExpectedDeliveryDateMessage } from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import { TIDs } from 'cypress/tids';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { twJoin } from 'tailwind-merge';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible } from 'utils/mappers/price';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

type TransportAndPaymentSelectItemLabelProps = {
    name: string;
    price?: { priceWithVat: string; priceWithoutVat: string; vatAmount: string };
    daysUntilDelivery?: number;
    expectedDeliveryDate?: string | null;
    description?: string | null;
    image?: TypeImageFragment | null;
    pickupPlaceDetail?: StoreOrPacketeryPoint;
    isActive?: boolean;
    isImageOnWhiteBackground?: boolean;
    disabled?: boolean;
    showChangeButton?: boolean;
    openingHoursDisplay?: TransportAndPaymentPickupPlaceOpeningHoursDisplay;
    pickupPlaceDetailLayout?: TransportAndPaymentPickupPlaceDetailLayout;
    openPickupPlacePopup?: () => void;
};

export const TransportAndPaymentSelectItemLabel: FC<TransportAndPaymentSelectItemLabelProps> = ({
    name,
    price,
    daysUntilDelivery,
    expectedDeliveryDate,
    description,
    image,
    pickupPlaceDetail,
    isActive,
    isImageOnWhiteBackground,
    disabled,
    showChangeButton,
    openingHoursDisplay = 'accordion',
    pickupPlaceDetailLayout = 'default',
    openPickupPlacePopup,
}) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const expectedDeliveryDateMessage = useExpectedDeliveryDateMessage(expectedDeliveryDate);

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
        <div className="ml-auto text-text-default">{formatPrice(price.priceWithVat)}</div>
    );

    return (
        <div className="flex w-full flex-col">
            <div className="flex flex-row items-center gap-3">
                {imageElement}

                <div className="flex min-w-0 flex-col gap-1">
                    <div
                        className="text-text-default"
                        data-tid={TIDs.pages_order_selectitem_label_name}
                        id={`${pickupPlaceDetail?.identifier}-${pickupPlaceDetail?.name}`}
                    >
                        {name}
                    </div>

                    {description && <div className="text-text-less text-xs">{description}</div>}

                    {!pickupPlaceDetail &&
                        expectedDeliveryDate !== undefined &&
                        (expectedDeliveryDateMessage !== null ? (
                            <div className="text-sm text-text-success">{expectedDeliveryDateMessage}</div>
                        ) : (
                            <div
                                className="flex items-center gap-1 text-sm text-text-less"
                                title={t(
                                    'The cart contains goods that are out of stock and we do not know their restocking date yet',
                                )}
                            >
                                <InfoIcon className="size-4 shrink-0" />
                                {t('The delivery date cannot be determined')}
                            </div>
                        ))}
                </div>

                {priceElement}
            </div>

            {pickupPlaceDetail && (
                <TransportAndPaymentPickupPlaceDetail
                    daysUntilDelivery={daysUntilDelivery}
                    disabled={disabled}
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
