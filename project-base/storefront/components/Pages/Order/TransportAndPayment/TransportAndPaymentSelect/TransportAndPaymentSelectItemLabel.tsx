import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { Image } from 'components/Basic/Image/Image';
import { OpeningHours } from 'components/Blocks/OpeningHours/OpeningHours';
import { getDeliveryMessage } from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import { TIDs } from 'cypress/tids';
import { AnimatePresence } from 'framer-motion';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

type TransportAndPaymentSelectItemLabelProps = {
    name: string;
    price?: { priceWithVat: string; priceWithoutVat: string; vatAmount: string };
    daysUntilDelivery?: number;
    description?: string | null;
    image?: TypeImageFragment | null;
    pickupPlaceDetail?: StoreOrPacketeryPoint;
    isActive?: boolean;
    showChangeButton?: boolean;
    openPickupPlacePopup?: () => void;
};

export const TransportAndPaymentSelectItemLabel: FC<TransportAndPaymentSelectItemLabelProps> = ({
    name,
    price,
    daysUntilDelivery,
    description,
    image,
    pickupPlaceDetail,
    isActive,
    showChangeButton,
    openPickupPlacePopup,
}) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();

    return (
        <div className="flex w-full flex-col gap-2">
            <div className="flex flex-row items-center gap-2">
                <div
                    data-tid={TIDs.transport_and_payment_list_item_image}
                    className={twJoin(
                        'bg-background-more isolate flex h-12 w-20 items-center justify-center rounded-xl',
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

                <div className="flex flex-col gap-1">
                    <div
                        data-tid={TIDs.pages_order_selectitem_label_name}
                        id={`${pickupPlaceDetail?.identifier}-${pickupPlaceDetail?.name}`}
                    >
                        {name}
                    </div>

                    {description && (
                        <div className={twJoin(pickupPlaceDetail ? 'text-text-default' : 'text-text-less', 'text-xs')}>
                            {description}
                        </div>
                    )}

                    {pickupPlaceDetail && (
                        <>
                            <span
                                className={twJoin(
                                    'text-xs',
                                    (showChangeButton || isActive) && !description
                                        ? 'text-text-default'
                                        : 'text-text-less',
                                )}
                            >
                                {pickupPlaceDetail.name}, {pickupPlaceDetail.city}, {pickupPlaceDetail.street}
                            </span>

                            <AnimatePresence initial={false}>
                                {isActive && (
                                    <AnimateCollapseDiv
                                        className="!block"
                                        keyName="store-opening-hours"
                                        onAnimationStart={() => {
                                            setTimeout(() => {
                                                document.getElementById(pickupPlaceDetail.identifier)?.scrollIntoView({
                                                    behavior: 'smooth',
                                                    block: 'start',
                                                });
                                            }, 300);
                                        }}
                                    >
                                        <OpeningHours openingHours={pickupPlaceDetail.openingHours} />
                                    </AnimateCollapseDiv>
                                )}
                            </AnimatePresence>
                        </>
                    )}

                    {daysUntilDelivery !== undefined && (
                        <div className="text-text-success hidden text-xs md:block">
                            {getDeliveryMessage(daysUntilDelivery, !!pickupPlaceDetail, t)}
                        </div>
                    )}

                    {showChangeButton && pickupPlaceDetail && (
                        <button
                            aria-haspopup="dialog"
                            className="text-link-default hover:text-link-hovered cursor-pointer text-left text-xs underline hover:no-underline"
                            tabIndex={0}
                            onClick={openPickupPlacePopup}
                        >
                            {t('Change pickup place')}
                        </button>
                    )}
                </div>

                {price && isPriceVisible(price.priceWithVat) && (
                    <div className={twJoin('text-text-default ml-auto', pickupPlaceDetail && 'hidden md:block')}>
                        {formatPrice(price.priceWithVat)}
                    </div>
                )}
            </div>

            {pickupPlaceDetail && (
                <div className="-ml-7 flex items-center justify-between md:hidden">
                    {daysUntilDelivery !== undefined && (
                        <div className="text-text-success text-xs">
                            {getDeliveryMessage(daysUntilDelivery, !!pickupPlaceDetail, t)}
                        </div>
                    )}

                    {price && isPriceVisible(price.priceWithVat) && (
                        <div className="text-text-default ml-auto">{formatPrice(price.priceWithVat)}</div>
                    )}
                </div>
            )}
        </div>
    );
};
