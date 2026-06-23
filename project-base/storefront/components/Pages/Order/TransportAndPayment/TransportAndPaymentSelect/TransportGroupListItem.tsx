import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import { AnimatePresence } from 'framer-motion';
import { TypeTransportWithAvailablePaymentsFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsFragment.generated';
import { KeyboardEvent, MouseEvent } from 'react';
import { twJoin } from 'tailwind-merge';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible } from 'utils/mappers/price';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { TransportListItem } from './TransportSelectListItem';

type ChangeTransport = (
    updatedTransportUuid: string | null,
    event: KeyboardEvent<HTMLInputElement> | MouseEvent<HTMLInputElement>,
) => void;

type TransportGroupListItemProps = {
    group: NonNullable<TypeTransportWithAvailablePaymentsFragment['group']>;
    transports: TypeTransportWithAvailablePaymentsFragment[];
    isSelected: boolean;
    isTransportSelectionLoading: boolean;
    pickupPlace: StoreOrPacketeryPoint | null;
    changeTransport: ChangeTransport;
    toggleSelectedTransportGroup: (transportGroupUuid: string) => void;
};

type TransportGroupPrice = {
    price: number;
    shouldDisplayFromPrefix: boolean;
};

const getTransportGroupPrice = (
    transports: TypeTransportWithAvailablePaymentsFragment[],
): TransportGroupPrice | null => {
    const visiblePrices = transports
        .filter((transport) => isPriceVisible(transport.price.priceWithVat))
        .map((transport) => Number.parseFloat(transport.price.priceWithVat));

    if (visiblePrices.length === 0) {
        return null;
    }

    const minPrice = Math.min(...visiblePrices);
    const hasDifferentPrices = new Set(visiblePrices).size > 1;

    return {
        price: minPrice,
        shouldDisplayFromPrefix: hasDifferentPrices,
    };
};

type TransportGroupIconProps = {
    group: NonNullable<TypeTransportWithAvailablePaymentsFragment['group']>;
    isOnGreyBackground?: boolean;
};

const TransportGroupIcon: FC<TransportGroupIconProps> = ({ group, isOnGreyBackground }) => {
    const wrapperClassName = twJoin(
        'flex h-10 w-14 min-w-14 items-center justify-center rounded-xl text-icon-accent',
        isOnGreyBackground ? 'bg-background-default' : 'bg-background-more',
    );

    if (!group.mainImage) {
        return null;
    }

    return (
        <span className={wrapperClassName}>
            <Image
                alt={group.mainImage.name ?? group.name}
                className="h-6 object-contain object-center mix-blend-multiply"
                height={24}
                src={group.mainImage.url}
                width={40}
            />
        </span>
    );
};

export const TransportGroupListItem: FC<TransportGroupListItemProps> = ({
    group,
    transports,
    isSelected,
    isTransportSelectionLoading,
    pickupPlace,
    changeTransport,
    toggleSelectedTransportGroup,
}) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const groupPrice = getTransportGroupPrice(transports);
    const transportGroupButtonId = `transport-group-${group.uuid}`;
    const transportGroupPanelId = `transport-group-panel-${group.uuid}`;

    return (
        <li
            key={group.uuid}
            className={twJoin(
                'group mb-4 overflow-hidden rounded-xl border transition last:mb-0',
                isSelected
                    ? 'border-border-less bg-background-default'
                    : 'border-transparent bg-background-more hover:border-border-less hover:bg-background-default',
            )}
        >
            <button
                aria-controls={transportGroupPanelId}
                aria-expanded={isSelected}
                className="flex w-full cursor-pointer items-center justify-between gap-4 px-4 vl:px-5 py-4 transition disabled:pointer-events-none disabled:opacity-50"
                data-tid={TIDs.transport_group_button}
                disabled={isTransportSelectionLoading}
                id={transportGroupButtonId}
                type="button"
                onClick={() => toggleSelectedTransportGroup(group.uuid)}
            >
                <span className="flex min-w-0 items-center gap-4">
                    <span
                        className={twJoin(
                            'flex size-5 min-w-5 items-center justify-center text-icon-accent transition',
                            isSelected && 'rotate-180',
                        )}
                        aria-hidden="true"
                    >
                        <ArrowIcon className="size-5" />
                    </span>

                    <TransportGroupIcon group={group} isOnGreyBackground={!isSelected} />

                    <span className="flex min-h-7 min-w-0 items-center text-left font-secondary font-semibold text-base text-text-default transition">
                        {group.name}
                    </span>
                </span>

                <span className="flex min-w-fit items-center gap-4">
                    {groupPrice !== null && (
                        <span className="whitespace-nowrap font-secondary font-semibold text-sm text-text-default">
                            {groupPrice.shouldDisplayFromPrefix && `${t('from')} `}
                            {formatPrice(groupPrice.price, { explicitZero: groupPrice.shouldDisplayFromPrefix })}
                        </span>
                    )}
                </span>
            </button>

            <AnimatePresence initial={false}>
                {isSelected && (
                    <AnimateCollapseDiv
                        aria-labelledby={transportGroupButtonId}
                        className="block! relative"
                        id={transportGroupPanelId}
                        keyName={transportGroupPanelId}
                        tid={TIDs.transport_group_panel}
                    >
                        <ul className="px-4 vl:px-5 pb-4 vl:pb-5">
                            {transports.map((transportItem) => (
                                <TransportListItem
                                    hasGreyBackground
                                    key={transportItem.uuid}
                                    changeTransport={changeTransport}
                                    disabled={
                                        isTransportSelectionLoading ||
                                        transportItem.productsBlockingSelectionInCart.length > 0
                                    }
                                    pickupPlace={pickupPlace}
                                    transport={transportItem}
                                />
                            ))}
                        </ul>
                    </AnimateCollapseDiv>
                )}
            </AnimatePresence>
        </li>
    );
};
