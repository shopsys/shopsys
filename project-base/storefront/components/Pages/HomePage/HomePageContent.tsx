import { SearchMetadata } from 'components/Basic/Head/SearchMetadata';
import { Banners } from 'components/Blocks/Banners/Banners';
import { DeferredBlogPreview } from 'components/Blocks/BlogPreview/DeferredBlogPreview';
import { PromotedCategories } from 'components/Blocks/Categories/PromotedCategories';
import { DeferredPromotedProducts } from 'components/Blocks/Product/DeferredPromotedProducts';
import { DeferredRecommendedProducts } from 'components/Blocks/Product/DeferredRecommendedProducts';
import { DeferredLastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts';
import { UpsList } from 'components/Blocks/UpsList/UpsList';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
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
                <VerticalStack gap="lg">
                    <h1 className="sr-only">{t('Shopsys.com')}</h1>

                    <Banners />

                    <UpsList />

                    <PromotedCategories />

                    {isLuigisBoxActive && (
                        <DeferredRecommendedProducts
                            recommendationType={TypeRecommendationType.Personalized}
                            render={(recommendedProductsContent) => (
                                <section aria-label={t('Recommended products')}>
                                    <h2 className="h3 mb-3">{t('Recommended for you')}</h2>
                                    {recommendedProductsContent}
                                </section>
                            )}
                        />
                    )}

                    <DeferredPromotedProducts />

                    <DeferredBlogPreview />

                    <DeferredLastVisitedProducts />
                </VerticalStack>
            </CommonLayout>
        </>
    );
};
