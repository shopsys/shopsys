import { HomepageMetadataJsonLd } from 'app/_components/Basic/Head/HomepageMetadataJsonLd';
import { BlogPreview } from 'app/_components/Blocks/BlogPreview/BlogPreview';
import { PromotedCategories } from 'app/_components/Blocks/Categories/PromotedCategories';
import { LastVisitedProducts } from 'app/_components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { PromotedProducts } from 'app/_components/Blocks/Product/PromotedProducts/PromotedProducts';
import { RecommendedProducts } from 'app/_components/Blocks/Product/RecommendedProducts/RecommendedProducts';
import { UpsList } from 'app/_components/Blocks/UpsList/UpsList';
import { getBlogArticles } from 'app/_queries/getBlogArticles';
import { getPromotedCategories } from 'app/_queries/getPromotedCategories';
import { getSettingsQuery } from 'app/_queries/getSettingsQuery';
import { getSliderItems } from 'app/_queries/getSliderItems';
import { getDomainConfig } from 'app/_utils/getDomainConfig';
import { Banners } from 'components/Blocks/Banners/Banners';
import { SkeletonModuleBanners } from 'components/Blocks/Skeleton/SkeletonModuleBanners';
import { SkeletonModuleMagazine } from 'components/Blocks/Skeleton/SkeletonModuleMagazine';
import { SkeletonModulePromotedCategories } from 'components/Blocks/Skeleton/SkeletonModulePromotedCategories';
import { Webline } from 'components/Layout/Webline/Webline';
import { BLOG_PREVIEW_VARIABLES } from 'config/constants';
import { TypeRecommendationType } from 'graphql/types';
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

            <RecommendedProducts recommendationType={TypeRecommendationType.Personalized} />

            <PromotedProducts />

            <Suspense fallback={<SkeletonModuleMagazine />}>
                <BlogPreview
                    blogPreviewData={blogPreviewArticlesData}
                    mainBlogCategoryData={settingsData?.settings?.mainBlogCategoryData}
                />
            </Suspense>

            <LastVisitedProducts />
        </>
    );
};

export default HomePage;
