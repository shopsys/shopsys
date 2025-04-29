import { Container } from './_components/Layout/Container/Container';
import { HomepageMetadataJsonLd } from 'app/_components/Basic/Head/HomepageMetadataJsonLd';
import { BlogPreview } from 'app/_components/Blocks/BlogPreview/BlogPreview';
import { PromotedCategories } from 'app/_components/Blocks/Categories/PromotedCategories';
import { LastVisitedProducts } from 'app/_components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { PromotedProducts } from 'app/_components/Blocks/Product/PromotedProducts/PromotedProducts';
import { RecommendedProducts } from 'app/_components/Blocks/Product/RecommendedProducts/RecommendedProducts';
import { UpsList } from 'app/_components/Blocks/UpsList/UpsList';
import { getDomainConfig } from 'app/_utils/getDomainConfig';
import { Banners } from 'components/Blocks/Banners/Banners';
import { TypeRecommendationType } from 'graphql/types';
import { headers } from 'next/headers';

const HomePage = async () => {
    // const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.homepage);
    // useGtmPageViewEvent(gtmStaticPageViewEvent);

    const domainConfig = getDomainConfig((await headers()).get('host')!);

    return (
        <>
            <HomepageMetadataJsonLd url={domainConfig.url} />

            <Container gap="large">
                <Banners />

                <UpsList />

                <PromotedCategories />

                <RecommendedProducts recommendationType={TypeRecommendationType.Personalized} />

                <PromotedProducts />

                <BlogPreview />

                <LastVisitedProducts />
            </Container>
        </>
    );
};

export default HomePage;
