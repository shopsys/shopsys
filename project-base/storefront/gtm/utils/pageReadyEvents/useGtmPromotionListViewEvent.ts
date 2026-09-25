import { useGtmContext } from 'gtm/context/GtmProvider';
import { getGtmPromotionListViewEvent } from 'gtm/factories/getGtmPromotionListViewEvent';
import { GtmPromotionType } from 'gtm/types/events';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { useEffect, useRef } from 'react';

export const useGtmPromotionListViewEvent = (promotions: GtmPromotionType[]): void => {
    const viewedPromotionIds = useRef<Set<string>>(new Set());
    const { didPageReadyRun, isScriptLoaded } = useGtmContext();

    useEffect(() => {
        const untrackedPromotions = promotions.filter(({ promotionId, creativeSlot }) => {
            const promotionKey = `${promotionId}-${creativeSlot}`;

            return !viewedPromotionIds.current.has(promotionKey);
        });

        if (isScriptLoaded && didPageReadyRun && untrackedPromotions.length) {
            untrackedPromotions.forEach(({ promotionId, creativeSlot }) => {
                viewedPromotionIds.current.add(`${promotionId}-${creativeSlot}`);
            });
            gtmSafePushEvent(getGtmPromotionListViewEvent(untrackedPromotions));
        }
    }, [promotions, didPageReadyRun, isScriptLoaded]);
};
