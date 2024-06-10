import { AdvertImage } from './AdvertImage';
import { Webline } from 'components/Layout/Webline/Webline';
import { useAdvertsQuery } from 'graphql/requests/adverts/queries/AdvertsQuery.generated';
import { TypeCategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import { twJoin } from 'tailwind-merge';

type PositionNameType =
    | 'productList'
    | 'footer'
    | 'header'
    | 'productListMiddle'
    | 'cartPreview'
    | 'productListSecondRow';

type AdvertsProps = {
    positionName: PositionNameType;
    withGapBottom?: boolean;
    withGapTop?: boolean;
    withWebline?: boolean;
    currentCategory?: TypeCategoryDetailFragment;
    isSingle?: boolean;
};

export const Adverts: FC<AdvertsProps> = ({
    positionName,
    withGapBottom,
    withGapTop,
    withWebline,
    currentCategory,
    className,
    isSingle,
}) => {
    const [{ data: advertsData }] = useAdvertsQuery({
        variables: {
            categoryUuid: currentCategory?.uuid || null,
            positionName,
        },
    });
    const adverts = advertsData?.adverts ?? [];
    const displayedAdverts =
        isSingle && adverts.length ? [adverts[Math.floor(Math.random() * adverts.length)]] : adverts;

    const content = !!displayedAdverts.length && (
        <div className={twJoin(withGapBottom && 'mb-8', withGapTop && 'mt-8', !withWebline && className)}>
            {displayedAdverts.map((advert) => {
                if (advert.__typename === 'AdvertImage') {
                    return <AdvertImage key={advert.uuid} advert={advert} />;
                }

                return <div key={advert.uuid} dangerouslySetInnerHTML={{ __html: advert.code }} />;
            })}
        </div>
    );

    if (withWebline && content) {
        return <Webline className={className}>{content}</Webline>;
    }

    return content || null;
};
