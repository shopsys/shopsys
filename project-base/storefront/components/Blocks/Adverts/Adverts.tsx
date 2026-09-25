import { Webline } from 'components/Layout/Webline/Webline';
import { TypeAdvertsFragment_AdvertCode } from 'graphql/requests/adverts/fragments/AdvertsFragment.generated';
import { useAdvertsQuery } from 'graphql/requests/adverts/queries/AdvertsQuery.generated';
import { TypeCategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import { onGtmPromotionClickEventHandler } from 'gtm/handlers/onGtmPromotionClickEventHandler';
import { GtmPromotionType } from 'gtm/types/events';
import { useGtmPromotionListViewEvent } from 'gtm/utils/pageReadyEvents/useGtmPromotionListViewEvent';
import { useEffect, useMemo, useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { AdvertImage } from './AdvertImage';

type PositionNameType = 'footer' | 'header' | 'cartPreview' | 'productListSecondRow';

type AdvertsProps = {
    positionName: PositionNameType;
    withWebline?: boolean;
    currentCategory?: TypeCategoryDetailFragment;
    isSingle?: boolean;
};

type AdvertCodeProps = {
    advert: TypeAdvertsFragment_AdvertCode;
    promotion: GtmPromotionType;
};

const AdvertCode: FC<AdvertCodeProps> = ({ advert, promotion }) => {
    const advertRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const advertElement = advertRef.current;

        const handleClick = (event: MouseEvent) => {
            if (!(event.target instanceof Element)) {
                return;
            }

            const link = event.target.closest('a[href]');
            const destinationURL = link?.getAttribute('href');

            if (destinationURL) {
                onGtmPromotionClickEventHandler(promotion, destinationURL);
            }
        };

        advertElement?.addEventListener('click', handleClick);

        return () => advertElement?.removeEventListener('click', handleClick);
    }, [promotion]);

    return <div ref={advertRef} className="promo-wrapper" dangerouslySetInnerHTML={{ __html: advert.code }} />;
};

export const Adverts: FC<AdvertsProps> = ({ positionName, withWebline, currentCategory, className, isSingle }) => {
    // setState lazy initializer runs once per mount; Strict Mode may call it twice in dev but discards one result, so the seed remains stable after mount
    const [randomSeed] = useState(() => Math.random());

    const [{ data: advertsData }] = useAdvertsQuery({
        variables: {
            categoryUuid: currentCategory?.uuid || null,
            positionNames: getPositionNames(positionName),
        },
    });

    const displayedAdverts = useMemo(() => {
        const advertsForPosition = advertsData?.adverts.filter((advert) => advert.positionName === positionName) ?? [];

        if (isSingle && advertsForPosition.length) {
            return [advertsForPosition[Math.floor(randomSeed * advertsForPosition.length)]];
        }
        return advertsForPosition;
    }, [isSingle, advertsData?.adverts, positionName, randomSeed]);

    const promotions: GtmPromotionType[] = displayedAdverts.map((advert) => ({
        promotionId: advert.id,
        promotionName: advert.positionName,
        creativeName: advert.name,
        creativeSlot:
            advert.positionName === 'productListSecondRow'
                ? currentCategory?.name
                : advert.positionName === 'cartPreview'
                  ? 'cart'
                  : undefined,
    }));

    useGtmPromotionListViewEvent(promotions);

    if (!displayedAdverts.length) {
        return null;
    }

    const content = (
        <div className={twJoin(!withWebline && className)}>
            {displayedAdverts.map((advert, index) => {
                if (advert.__typename === 'AdvertImage') {
                    return <AdvertImage key={advert.uuid} advert={advert} promotion={promotions[index]} />;
                }

                return <AdvertCode key={advert.uuid} advert={advert} promotion={promotions[index]} />;
            })}
        </div>
    );

    if (withWebline) {
        return <Webline className={className}>{content}</Webline>;
    }

    return content;
};

const getPositionNames = (positionName: PositionNameType) => {
    if (positionName === 'header' || positionName === 'footer') {
        return ['header', 'footer'];
    }

    if (positionName === 'productListSecondRow') {
        return ['productListSecondRow'];
    }

    return ['cartPreview'];
};
