import { SearchMetadata } from 'components/Basic/Head/SearchMetadata';
import { Banners } from 'components/Blocks/Banners/Banners';
import { DeferredBlogPreview } from 'components/Blocks/BlogPreview/DeferredBlogPreview';
import { PromotedCategories } from 'components/Blocks/Categories/PromotedCategories';
import { DeferredPromotedProducts } from 'components/Blocks/Product/DeferredPromotedProducts';
import { DeferredRecommendedProducts } from 'components/Blocks/Product/DeferredRecommendedProducts';
import { DeferredLastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeRecommendationType } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';

export const HomePageContent: FC = () => {
    const { t } = useTranslation();
    const { isLuigisBoxActive } = useDomainConfig();

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.homepage);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <SearchMetadata />
            <CommonLayout>
                <Banners />

                <PromotedCategories />

                {isLuigisBoxActive && (
                    <DeferredRecommendedProducts
                        recommendationType={TypeRecommendationType.Personalized}
                        render={(recommendedProductsContent) => (
                            <Webline className="mb-10">
                                <h3 className="mb-4">{t('Recommended for you')}</h3> {recommendedProductsContent}
                            </Webline>
                        )}
                    />
                )}

                <DeferredPromotedProducts />

                <DeferredBlogPreview />

                <DeferredLastVisitedProducts />
            </CommonLayout>
        </>
    );
};
