import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Webline } from 'components/Layout/Webline/Webline';
import { AdvertsFragmentApi, CategoryDetailFragmentApi, useAdvertsQueryApi } from 'graphql/generated';
import { Fragment } from 'react';
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
    currentCategory?: CategoryDetailFragmentApi;
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
    const [{ data: advertsData }] = useAdvertsQueryApi();

    const filteredAdverts = advertsData?.adverts.filter((advert) => advert.positionName === positionName);
    const displayedAdverts =
        isSingle && filteredAdverts?.length
            ? [filteredAdverts[Math.floor(Math.random() * filteredAdverts.length)]]
            : filteredAdverts;

    const content = !!displayedAdverts?.length && (
        <div className={twJoin(withGapBottom && 'mb-8', withGapTop && 'mt-8', !withWebline && className)}>
            {displayedAdverts.map((advert, index) => {
                if (advert.__typename === 'AdvertImage') {
                    if (!shouldBeShown(advert, positionName, currentCategory)) {
                        return null;
                    }

                    const mainImage = advert.mainImage?.sizes.find(({ size }) => size === positionName);
                    const mainImageMobile = advert.mainImageMobile?.sizes.find(({ size }) => size === positionName);

                    const ImageComponent = (
                        <picture>
                            {/* use min-width equal to Tailwind "lg" breakpoint */}
                            <source srcSet={mainImage?.url} media="(min-width: 48.0625em)" />
                            <img
                                src={mainImageMobile?.url}
                                alt={advert.mainImage?.name || advert.mainImageMobile?.name || advert.name}
                                width={mainImageMobile?.width || undefined}
                                height={mainImageMobile?.height || undefined}
                                className="w-full"
                            />
                        </picture>
                    );

                    return (
                        <Fragment key={index}>
                            {advert.link ? (
                                <ExtendedNextLink href={advert.link} target="_blank" type="static">
                                    {ImageComponent}
                                </ExtendedNextLink>
                            ) : (
                                ImageComponent
                            )}
                        </Fragment>
                    );
                }

                return <div dangerouslySetInnerHTML={{ __html: advert.code }} key={index} />;
            })}
        </div>
    );

    if (withWebline && content) {
        return <Webline className={className}>{content}</Webline>;
    }

    return content || null;
};

const shouldBeShown = (
    advert: AdvertsFragmentApi | undefined,
    positionName: PositionNameType,
    currentCategory?: CategoryDetailFragmentApi,
): boolean => {
    if (!advert || advert.positionName !== positionName) {
        return false;
    }

    if (advert.positionName === 'productListMiddle' && !advert.categories.length) {
        return false;
    }

    for (const category of advert.categories) {
        if (category.slug === currentCategory?.slug || category.slug === currentCategory?.originalCategorySlug) {
            return true;
        }
    }

    return positionName !== 'productListMiddle' && advert.positionName === positionName;
};
