import { SearchMetadata } from 'components/Basic/Head/SearchMetadata';
import { Banners } from 'components/Blocks/Banners/Banners';
import { DeferredBlogPreview } from 'components/Blocks/BlogPreview/DeferredBlogPreview';
import { PromotedCategories } from 'components/Blocks/Categories/PromotedCategories';
import { DeferredPromotedProducts } from 'components/Blocks/Product/DeferredPromotedProducts';
import { DeferredRecommendedProducts } from 'components/Blocks/Product/DeferredRecommendedProducts';
import { DeferredLastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts';
import { UspList } from 'components/Blocks/UspList/UspList';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeRecommendationType } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageReadyEvent } from 'gtm/factories/useGtmStaticPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const HomePageContent: FC = () => {
    const { t } = useTranslation();
    const { isLuigisBoxActive } = useDomainConfig();

    const gtmStaticPageReadyEvent = useGtmStaticPageReadyEvent(GtmPageType.homepage);
    useGtmPageReadyEvent(gtmStaticPageReadyEvent);

    return (
        <>
            <SearchMetadata />

            <CommonLayout>
                <VerticalStack gap="lg">
                    <h1 className="sr-only">{t('Shopsys.com')}</h1>

                    <Banners />

                    <UspList />

                    <PromotedCategories />

                    {isLuigisBoxActive && (
                        <DeferredRecommendedProducts
                            recommendationType={TypeRecommendationType.Personalized}
                            render={(recommendedProductsContent) => (
                                <Webline>
                                    <h2 className="h3 mb-3">{t('Recommended for you')}</h2>
                                    {recommendedProductsContent}
                                </Webline>
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
