import { TIDs } from 'cypress/tids';
import { AnimatePresence } from 'framer-motion';
import { useEffect, useRef, useState } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { twMergeCustom } from 'utils/twMerge';
import { StoreExpandedInfo } from './StoreExpandedInfo';
import { StoreInfoToggleButton } from './StoreInfoToggleButton';
import { StoreSummary } from './StoreSummary';

type StoreListItemProps = {
    store: StoreOrPacketeryPoint;
    isSelected: boolean;
    isDistanceFromSearchText: boolean;
    mode?: 'default' | 'selectOnItemClick';
    unknownDeliveryDateExplanation?: string;
    onSelectStoreCallback?: (storeUuid: string | null) => void;
};

export const StoreListItem: FC<StoreListItemProps> = ({
    store,
    isSelected,
    isDistanceFromSearchText,
    mode = 'default',
    unknownDeliveryDateExplanation,
    onSelectStoreCallback,
}) => {
    const isSelectionMode = mode === 'selectOnItemClick' && onSelectStoreCallback !== undefined;
    const [isExpanded, setIsExpanded] = useState(!isSelectionMode && isSelected);
    const { t } = useTranslation();
    const itemRef = useRef<HTMLDivElement>(null);
    const storeInfoId = `store-info-${store.slug.replace(/\//g, '-')}`;
    const distance = store.distance;
    const hasTodayOpeningHours = store.openingHours.openingHoursOfDays[0].openingHoursRanges.length > 0;

    useEffect(() => {
        if (!isSelectionMode) {
            setIsExpanded(isSelected);
        }
    }, [isSelected, isSelectionMode]);

    useEffect(() => {
        if (isExpanded && itemRef.current) {
            const timeoutId = setTimeout(() => {
                itemRef.current?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                    inline: 'end',
                });
            }, 100);
            return () => clearTimeout(timeoutId);
        }

        return undefined;
    }, [isExpanded]);

    const toggleStoreInfoHandler = () => {
        setIsExpanded((currentIsExpanded) => !currentIsExpanded);
    };

    const selectStoreHandler = () => {
        onSelectStoreCallback?.(store.identifier);
    };
    const runSummaryAction = isSelectionMode ? selectStoreHandler : toggleStoreInfoHandler;
    const summaryClickHandler = (event: React.MouseEvent<HTMLDivElement>) => {
        event.stopPropagation();
        runSummaryAction();
    };
    const summaryKeyDownHandler = (event: React.KeyboardEvent<HTMLDivElement>) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            runSummaryAction();
        }
    };
    const formattedDistance =
        distance !== null && distance !== undefined
            ? isDistanceFromSearchText
                ? t('{{ distance }} km away', {
                      distance: (distance / 1000).toFixed(0),
                  })
                : t('{{ distance }} km from you', {
                      distance: (distance / 1000).toFixed(0),
                  })
            : null;

    return (
        <div
            data-tid={`${TIDs.store_list_item_}${store.identifier}`}
            ref={itemRef}
            className={twMergeCustom(
                'rounded-xl border border-transparent bg-background-more px-5 py-2.5 text-left transition-colors hover:border-border-brand hover:bg-background-default has-focus-visible:border-border-brand has-focus-visible:bg-background-default',
                isSelected && 'border-border-brand bg-background-default',
                !isSelected && isExpanded && 'border-border-less',
            )}
        >
            <div aria-label={t('Store info', { ns: 'accessibility' })} className="flex items-center justify-between">
                <StoreSummary
                    formattedDistance={formattedDistance}
                    unknownDeliveryDateExplanation={unknownDeliveryDateExplanation}
                    hasTodayOpeningHours={hasTodayOpeningHours}
                    isExpanded={isExpanded}
                    isSelected={isSelected}
                    isSelectionMode={isSelectionMode}
                    store={store}
                    storeInfoId={storeInfoId}
                    onClick={summaryClickHandler}
                    onKeyDown={summaryKeyDownHandler}
                />

                <StoreInfoToggleButton
                    isExpanded={isExpanded}
                    store={store}
                    storeInfoId={storeInfoId}
                    onClick={toggleStoreInfoHandler}
                />
            </div>

            <div id={storeInfoId}>
                <AnimatePresence initial={false}>
                    {isExpanded && (
                        <StoreExpandedInfo
                            isSelected={isSelected}
                            keyName={storeInfoId}
                            shouldShowSelectButton={onSelectStoreCallback !== undefined && !isSelectionMode}
                            store={store}
                            onSelectStoreCallback={onSelectStoreCallback}
                        />
                    )}
                </AnimatePresence>
            </div>
        </div>
    );
};
