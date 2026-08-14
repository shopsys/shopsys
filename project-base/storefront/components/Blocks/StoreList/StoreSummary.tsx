import { ExpectedDeliveryDateInfo } from 'components/Blocks/ExpectedDeliveryDateInfo/ExpectedDeliveryDateInfo';
import OpeningHoursToday from 'components/Blocks/OpeningHours/OpeningHoursToday';
import { OpeningStatus } from 'components/Blocks/OpeningHours/OpeningStatus';
import { TIDs } from 'cypress/tids';
import { type KeyboardEvent, type MouseEvent } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { twMergeCustom } from 'utils/twMerge';

type StoreSummaryProps = {
    store: StoreOrPacketeryPoint;
    storeInfoId: string;
    formattedDistance: string | null;
    isExpanded: boolean;
    isSelected: boolean;
    hasTodayOpeningHours: boolean;
    isSelectionMode: boolean;
    unknownDeliveryDateExplanation?: string;
    onClick: (event: MouseEvent<HTMLDivElement>) => void;
    onKeyDown: (event: KeyboardEvent<HTMLDivElement>) => void;
};

export const StoreSummary: FC<StoreSummaryProps> = ({
    store,
    storeInfoId,
    formattedDistance,
    isExpanded,
    isSelected,
    hasTodayOpeningHours,
    isSelectionMode,
    unknownDeliveryDateExplanation,
    onClick,
    onKeyDown,
}) => {
    const { t } = useTranslation();
    const ariaLabel = isSelectionMode
        ? t('Select store {{storeName}}', { ns: 'accessibility', storeName: store.name })
        : isExpanded
          ? t('Collapse store info {{storeName}}', { ns: 'accessibility', storeName: store.name })
          : t('Expand store info {{storeName}}', { ns: 'accessibility', storeName: store.name });
    const title = isSelectionMode ? t('Select store') : isExpanded ? t('Collapse store info') : t('Expand store info');

    return (
        // biome-ignore lint/a11y: The summary behaves as a radio in pickup selection mode and as an expandable button on the stores page.
        <div
            aria-checked={isSelectionMode ? isSelected : undefined}
            aria-controls={storeInfoId}
            aria-expanded={isSelectionMode ? undefined : isExpanded}
            aria-label={ariaLabel}
            className={twMergeCustom(
                'group flex w-full cursor-pointer items-center gap-2.5 pr-4 text-left outline-hidden',
                !isExpanded && '-my-2.5 -ml-5 py-2.5 pl-5',
            )}
            data-tid={isSelectionMode ? TIDs.store_select_button : undefined}
            role={isSelectionMode ? 'radio' : 'button'}
            tabIndex={0}
            title={title}
            onClick={onClick}
            onKeyDown={onKeyDown}
        >
            {isSelectionMode && <StoreSelectionIndicator isSelected={isSelected} />}

            <div className="flex flex-1 flex-col justify-between gap-1 md:flex-row md:gap-4">
                <div className="flex flex-col gap-1">
                    <span className="h5">{store.name}</span>

                    <p aria-label={t('Address', { ns: 'accessibility' })} className="text-xs">
                        {store.street}, {store.postcode} {store.city}
                    </p>

                    {formattedDistance !== null && (
                        <p className="whitespace-nowrap text-input-placeholder-default text-xs">{formattedDistance}</p>
                    )}

                    {store.expectedDeliveryDate !== undefined && (
                        <ExpectedDeliveryDateInfo
                            isPersonalPickup
                            className="text-xs"
                            expectedDeliveryDate={store.expectedDeliveryDate}
                            unknownDeliveryDateExplanation={unknownDeliveryDateExplanation}
                        />
                    )}
                </div>

                <div className="flex flex-wrap items-center gap-1 md:flex-col md:items-end">
                    <span className="shrink-0" data-tid={TIDs.store_opening_status}>
                        <OpeningStatus status={store.openingHours.status} />
                    </span>

                    {hasTodayOpeningHours && (
                        <OpeningHoursToday
                            className="ml-0 shrink-0 whitespace-nowrap text-xs"
                            openingHours={store.openingHours}
                        />
                    )}
                </div>
            </div>
        </div>
    );
};

const StoreSelectionIndicator: FC<{ isSelected: boolean }> = ({ isSelected }) => (
    <span
        aria-hidden="true"
        className={twMergeCustom(
            'flex size-5 min-w-5 rounded-full border-2 bg-input-bg-default p-1.25 transition xl:mt-0',
            isSelected ? 'border-input-border-active bg-input-fill' : 'border-input-border-default',
        )}
    >
        <span
            className={twMergeCustom(
                'h-full w-full rounded-full bg-icon-inverted opacity-0 transition',
                isSelected && 'opacity-100',
            )}
        />
    </span>
);
