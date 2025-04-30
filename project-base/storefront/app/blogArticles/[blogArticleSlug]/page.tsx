import { GrapesJsParser } from 'app/_components/Basic/UserText/GrapesJsParser';
import { getBlogArticleDetailQuery } from 'app/_queries/getBlogArticleDetailQuery';
import { getFormatDate } from 'app/_utils/formatting/getFormatDate';
import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import { notFound } from 'next/navigation';
import { VISIBLE_SLIDER_ITEMS_BLOG } from 'utils/productSlider';

const BlogArticleDetailPage = async ({ params }: { params: Promise<{ blogArticleSlug: string }> }) => {
    const { blogArticleSlug } = await params;
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
        </>
    );
};

export default BlogArticleDetailPage;
