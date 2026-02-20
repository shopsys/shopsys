import { AdvertImage } from './AdvertImage';
import { Webline } from 'components/Layout/Webline/Webline';
import { useAdvertsQuery } from 'graphql/requests/adverts/queries/AdvertsQuery.generated';
import { TypeCategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import { useMemo, useState } from 'react';
import { twJoin } from 'tailwind-merge';

type PositionNameType = 'footer' | 'header' | 'cartPreview' | 'productListSecondRow';

type AdvertsProps = {
    positionName: PositionNameType;
    withWebline?: boolean;
    currentCategory?: TypeCategoryDetailFragment;
    isSingle?: boolean;
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

    if (!displayedAdverts.length) {
        return null;
    }

    const content = (
        <div className={twJoin(!withWebline && className)}>
            {displayedAdverts.map((advert) => {
                if (advert.__typename === 'AdvertImage') {
                    return <AdvertImage key={advert.uuid} advert={advert} />;
                }

                return (
                    <div
                        key={advert.uuid}
                        className="promo-wrapper"
                        dangerouslySetInnerHTML={{ __html: advert.code }}
                    />
                );
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
