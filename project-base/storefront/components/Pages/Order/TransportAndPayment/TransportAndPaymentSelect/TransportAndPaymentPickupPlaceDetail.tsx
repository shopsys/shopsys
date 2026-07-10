import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { OpeningHours } from 'components/Blocks/OpeningHours/OpeningHours';
import { getDeliveryMessage } from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import { AnimatePresence } from 'framer-motion';
import { ButtonHTMLAttributes, MouseEvent, ReactNode, useId, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

export type TransportAndPaymentPickupPlaceOpeningHoursDisplay = 'accordion' | 'expanded';
export type TransportAndPaymentPickupPlaceDetailLayout = 'default' | 'compact';

type TransportAndPaymentPickupPlaceDetailProps = {
    pickupPlace: StoreOrPacketeryPoint;
    daysUntilDelivery?: number;
    isActive?: boolean;
    disabled?: boolean;
    showChangeButton?: boolean;
    openingHoursDisplay?: TransportAndPaymentPickupPlaceOpeningHoursDisplay;
    layout?: TransportAndPaymentPickupPlaceDetailLayout;
    openPickupPlacePopup?: () => void;
};

type TransportAndPaymentPickupPlaceDetailActionButtonProps = ButtonHTMLAttributes<HTMLButtonElement> & {
    children: ReactNode;
};

const TransportAndPaymentPickupPlaceDetailActionButton: FC<TransportAndPaymentPickupPlaceDetailActionButtonProps> = ({
    children,
    className,
    type = 'button',
    ...props
}) => (
    <button
        className={twJoin(
            'group flex cursor-pointer items-center gap-1 rounded-sm text-left text-sm underline hover:no-underline disabled:pointer-events-none disabled:opacity-50',
            className,
        )}
        type={type}
        {...props}
    >
        {children}
    </button>
);

export const TransportAndPaymentPickupPlaceDetail: FC<TransportAndPaymentPickupPlaceDetailProps> = ({
    pickupPlace,
    daysUntilDelivery,
    isActive,
    disabled,
    showChangeButton,
    openingHoursDisplay = 'accordion',
    layout = 'default',
    openPickupPlacePopup,
}) => {
    const { t } = useTranslation();
    const openingHoursId = useId();
    const [areOpeningHoursExpanded, setAreOpeningHoursExpanded] = useState(false);

    const stopLabelClick = (event: MouseEvent<HTMLButtonElement>) => {
        event.preventDefault();
        event.stopPropagation();
    };

    const openPickupPlacePopupWithoutChangingTransport = (event: MouseEvent<HTMLButtonElement>) => {
        stopLabelClick(event);
        openPickupPlacePopup?.();
    };

    const toggleOpeningHours = (event: MouseEvent<HTMLButtonElement>) => {
        stopLabelClick(event);
        setAreOpeningHoursExpanded((currentValue) => !currentValue);
    };

    return (
        <div className={twJoin('flex flex-col', layout !== 'compact' && 'mt-2 gap-2 md:gap-1')}>
            <span className={twJoin('text-sm', showChangeButton || isActive ? 'text-text-default' : 'text-text-less')}>
                {pickupPlace.name}, {pickupPlace.city}, {pickupPlace.street}
            </span>

            {daysUntilDelivery !== undefined && (
                <div className="text-sm text-text-success">{getDeliveryMessage(daysUntilDelivery, true, t)}</div>
            )}

            <div
                className={twJoin(
                    'flex items-end justify-between gap-2 md:flex-row',
                    areOpeningHoursExpanded && 'flex-col',
                )}
            >
                {openingHoursDisplay === 'accordion' && (
                    <div className={twJoin('flex flex-col md:w-auto', areOpeningHoursExpanded && 'w-full')}>
                        <TransportAndPaymentPickupPlaceDetailActionButton
                            aria-controls={openingHoursId}
                            aria-expanded={areOpeningHoursExpanded}
                            disabled={disabled}
                            onClick={toggleOpeningHours}
                        >
                            {t('Opening hours')}

                            <ArrowIcon
                                aria-hidden="true"
                                className={twJoin(
                                    'size-4 min-w-4 text-link-default transition group-hover:text-link-hovered',
                                    areOpeningHoursExpanded && 'rotate-180',
                                )}
                            />
                        </TransportAndPaymentPickupPlaceDetailActionButton>

                        <AnimatePresence initial={false}>
                            {areOpeningHoursExpanded && (
                                <AnimateCollapseDiv
                                    className="block! mt-3"
                                    id={openingHoursId}
                                    keyName="store-opening-hours"
                                >
                                    <OpeningHours
                                        className="w-full"
                                        openingHours={pickupPlace.openingHours}
                                        variant="compact"
                                    />
                                </AnimateCollapseDiv>
                            )}
                        </AnimatePresence>
                    </div>
                )}

                {showChangeButton && (
                    <TransportAndPaymentPickupPlaceDetailActionButton
                        aria-haspopup="dialog"
                        disabled={disabled}
                        onClick={openPickupPlacePopupWithoutChangingTransport}
                    >
                        {t('Change pickup place')}
                    </TransportAndPaymentPickupPlaceDetailActionButton>
                )}
            </div>

            {isActive && openingHoursDisplay === 'expanded' && <OpeningHours openingHours={pickupPlace.openingHours} />}
        </div>
    );
};
