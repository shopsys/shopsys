import { HomepageMetadataJsonLd } from './_components/Basic/Head/HomepageMetadataJsonLd';
import { BlogPreview } from './_components/Blocks/BlogPreview/BlogPreview';
import { PromotedCategories } from './_components/Blocks/Categories/PromotedCategories';
import { getBlogArticles } from './_queries/getBlogArticles';
import { getPromotedCategories } from './_queries/getPromotedCategories';
import { getSettingsQuery } from './_queries/getSettingsQuery';
import { getSliderItems } from './_queries/getSliderItems';
import { getDomainConfig } from './_utils/getDomainConfig';
import { UpsList } from 'app/_components/Blocks/UpsList/UpsList';
import { Banners } from 'components/Blocks/Banners/Banners';
import { SkeletonModuleBanners } from 'components/Blocks/Skeleton/SkeletonModuleBanners';
import { SkeletonModuleMagazine } from 'components/Blocks/Skeleton/SkeletonModuleMagazine';
import { SkeletonModulePromotedCategories } from 'components/Blocks/Skeleton/SkeletonModulePromotedCategories';
import { Webline } from 'components/Layout/Webline/Webline';
import { BLOG_PREVIEW_VARIABLES } from 'config/constants';
import { headers } from 'next/headers';
import { Suspense } from 'react';

const HomePage = async () => {
    // const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.homepage);
    // useGtmPageViewEvent(gtmStaticPageViewEvent);

    const domainConfig = getDomainConfig(headers().get('host')!);
    const [promotedCategoriesResult, sliderItemsResult, blogPreviewArticlesResult, settingsResult] =
        await Promise.allSettled([
            getPromotedCategories(),
            getSliderItems(),
            getBlogArticles(BLOG_PREVIEW_VARIABLES),
            getSettingsQuery(),
        ]);

    const promotedCategoriesData =
        promotedCategoriesResult.status === 'fulfilled' ? promotedCategoriesResult.value : null;
    const sliderItemsData = sliderItemsResult.status === 'fulfilled' ? sliderItemsResult.value : null;
    const blogPreviewArticlesData =
        blogPreviewArticlesResult.status === 'fulfilled' ? blogPreviewArticlesResult.value : null;

    const settingsData = settingsResult.status === 'fulfilled' ? settingsResult.value : null;

    // { query: PromotedProductsQueryDocument },
    // ...(domainConfig.isLuigisBoxActive
    //     ? [
    //             {
    //                 query: RecommendedProductsQueryDocument,
    //                 variables: {
    //                     itemUuids: [],
    //                     userIdentifier: cookiesStoreState.userIdentifier,
    //                     recommendationType: TypeRecommendationType.Personalized,
    //                     recommenderClientIdentifier: getRecommenderClientIdentifier(context.resolvedUrl),
    //                     limit: 10,
    //                 },
    //             },
    //         ]
    //     : []),

    return (
        <>
            <HomepageMetadataJsonLd url={domainConfig.url} />
            <Suspense fallback={<SkeletonModuleBanners />}>
                <Banners sliderItemsData={sliderItemsData} />
            </Suspense>

            <UpsList />

            <Suspense
                fallback={
                    <Webline className="mb-10">
                        <SkeletonModulePromotedCategories />
                    </Webline>
                }
            >
                <PromotedCategories promotedCategoriesData={promotedCategoriesData} />
            </Suspense>

            {/* TODO: implement product sliders */}

            {/* {isLuigisBoxActive && (
                <DeferredRecommendedProducts
                    recommendationType={TypeRecommendationType.Personalized}
                    render={(recommendedProductsContent) => (
                        <>
                            <h3 className="mb-4">{t('Recommended for you')}</h3>
                            {recommendedProductsContent}
                        </>
                    )}
                />
            )} */}

            {/* <DeferredPromotedProducts /> */}

            <Suspense fallback={<SkeletonModuleMagazine />}>
                <BlogPreview
                    blogPreviewData={blogPreviewArticlesData}
                    mainBlogCategoryData={settingsData?.settings?.mainBlogCategoryData}
                />
            </Suspense>

            {/* <DeferredLastVisitedProducts /> */}
        </>
    );
};

export default HomePage;
