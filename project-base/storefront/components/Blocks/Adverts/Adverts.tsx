import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
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
            {displayedAdverts
                .filter((advert) => shouldBeShown(advert, positionName, currentCategory))
                .map((advert, index) => {
                    if (advert.__typename === 'AdvertImage') {
                        const mainImage = advert.mainImage;
                        const mainImageMobile = advert.mainImageMobile;

                        const ImageComponent = (
                            <>
                                <Image
                                    alt={mainImage?.name || advert.name}
                                    className="hidden lg:block"
                                    height={400}
                                    src={mainImage?.url}
                                    width={1280}
                                />
                                <Image
                                    alt={mainImageMobile?.name || advert.name}
                                    className="lg:hidden"
                                    height={300}
                                    src={mainImageMobile?.url}
                                    width={770}
                                />
                            </>
                        );

                        return (
                            <Fragment key={index}>
                                {advert.link ? (
                                    <ExtendedNextLink href={advert.link} target="_blank">
                                        {ImageComponent}
                                    </ExtendedNextLink>
                                ) : (
                                    ImageComponent
                                )}
                            </Fragment>
                        );
                    }

                    return <div key={index} dangerouslySetInnerHTML={{ __html: advert.code }} />;
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

    const isAdvertWithCategoryRestriction = ['productList', 'productListMiddle', 'productListSecondRow'].includes(
        advert.positionName,
    );

    if (isAdvertWithCategoryRestriction && !advert.categories.length) {
        return false;
    }

    for (const category of advert.categories) {
        if (category.slug === currentCategory?.slug || category.slug === currentCategory?.originalCategorySlug) {
            return true;
        }
    }

    return !isAdvertWithCategoryRestriction && advert.positionName === positionName;
};
