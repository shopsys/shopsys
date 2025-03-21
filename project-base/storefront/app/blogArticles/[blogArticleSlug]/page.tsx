import { GrapesJsParser } from 'app/_components/Basic/UserText/GrapesJsParser';
import { ArticleTitle } from 'app/_components/Blocks/Article/ArticleTitle';
import { LastVisitedProducts } from 'app/_components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { BlogLayout } from 'app/_components/Layout/BlogLayout';
import { getBlogArticleDetailQuery } from 'app/_queries/getBlogArticleDetailQuery';
import { getFormatDate } from 'app/_utils/formatting/getFormatDate';
import { Image } from 'components/Basic/Image/Image';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { notFound } from 'next/navigation';
import { VISIBLE_SLIDER_ITEMS_BLOG } from 'utils/productSlider';

const BlogArticleDetailPage = async ({ params: { blogArticleSlug } }: { params: { blogArticleSlug: string } }) => {
    const blogArticleData = await getBlogArticleDetailQuery(blogArticleSlug);

    if (!blogArticleData) {
        return notFound();
    }

    // const blogArticleImageUrl = blogArticleData?.mainImage?.url;

    // const pageViewEvent = useGtmFriendlyPageViewEvent(blogArticleData);
    // useGtmPageViewEvent(pageViewEvent, isBlogArticleFetching);
    const { formatDate } = await getFormatDate();
    return (
        <>
            {/* breadcrumbs={blogArticleData?.breadcrumb}
            breadcrumbsType="blogCategory"
            canonicalQueryParams={[]}
            description={blogArticleData?.seoMetaDescription}
            hreflangLinks={blogArticleData?.hreflangLinks}
            ogImageUrlDefault={blogArticleImageUrl}
            ogType={OgTypeEnum.Article}
            title={blogArticleData?.seoTitle || blogArticleData?.name} */}

            <BlogLayout activeCategoryUuid={blogArticleData.mainBlogCategoryUuid}>
                <ArticleTitle>{blogArticleData.seoH1 || blogArticleData.name}</ArticleTitle>
                <div className="mb-12 flex flex-col">
                    {blogArticleData.mainImage && (
                        <div className="mb-10 flex overflow-hidden">
                            <Image
                                priority
                                alt={blogArticleData.mainImage.name || blogArticleData.name}
                                height={600}
                                src={blogArticleData.mainImage.url}
                                width={1280}
                            />
                        </div>
                    )}

                    <div
                        className="text-textDisabled mb-2 text-left text-xs font-semibold"
                        tid={TIDs.blog_article_publication_date}
                    >
                        {formatDate(blogArticleData.publishDate, 'l')}
                    </div>

                    {!!blogArticleData.text && (
                        <GrapesJsParser text={blogArticleData.text} visibleSliderItems={VISIBLE_SLIDER_ITEMS_BLOG} />
                    )}
                </div>
            </BlogLayout>
            <Webline>
                <LastVisitedProducts />
            </Webline>
        </>
    );
};

export default BlogArticleDetailPage;
