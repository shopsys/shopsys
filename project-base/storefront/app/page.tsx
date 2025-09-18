import { BlogPreview } from './_components/Blocks/BlogPreview/BlogPreview';
import { HomepageMetadataJsonLd } from 'app/_components/Basic/Head/HomepageMetadataJsonLd';
import { PromotedCategories } from 'app/_components/Blocks/Categories/PromotedCategories';
import { LastVisitedProducts } from 'app/_components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { PromotedProducts } from 'app/_components/Blocks/Product/PromotedProducts/PromotedProducts';
import { RecommendedProducts } from 'app/_components/Blocks/Product/RecommendedProducts/RecommendedProducts';
import { UpsList } from 'app/_components/Blocks/UpsList/UpsList';
import { Banners } from 'components/Blocks/Banners/Banners';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { TypeRecommendationType } from 'graphql/types';

const HomePage = async () => {
    // const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.homepage);
    // useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <HomepageMetadataJsonLd />

            <VerticalStack gap="lg">
                <Banners />

                <UpsList />

                <PromotedCategories />

                <RecommendedProducts recommendationType={TypeRecommendationType.Personalized} />

                <PromotedProducts />

                <BlogPreview />

                <LastVisitedProducts />
            </VerticalStack>
        </>
    );
};

export default HomePage;
