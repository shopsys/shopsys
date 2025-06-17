import { ArticleDate } from 'components/Basic/ArticleDate/ArticleDate';
import { Flag } from 'components/Basic/Flag/Flag';
import { Image } from 'components/Basic/Image/Image';
import { GrapesJsParser } from 'components/Basic/UserText/GrapesJsParser';
import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { VISIBLE_SLIDER_ITEMS_ARTICLE } from 'components/Blocks/Product/ProductsSlider';
import { BlogLayout } from 'components/Layout/BlogLayout';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { TIDs } from 'cypress/tids';
import { TypeBlogArticleDetailFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleDetailFragment.generated';

type BlogArticleDetailContentProps = {
    blogArticle: TypeBlogArticleDetailFragment;
};

export const BlogArticleDetailContent: FC<BlogArticleDetailContentProps> = ({ blogArticle }) => {
    return (
        <VerticalStack gap="md">
            <BlogLayout activeCategoryUuid={blogArticle.mainBlogCategoryUuid}>
                <h1>{blogArticle.seoH1 || blogArticle.name}</h1>

                {blogArticle.mainImage && (
                    <div className="flex overflow-hidden">
                        <Image
                            priority
                            alt={blogArticle.mainImage.name || blogArticle.name}
                            height={600}
                            src={blogArticle.mainImage.url}
                            width={1280}
                        />
                    </div>
                )}

                <div className="flex flex-wrap items-center gap-x-1.5 gap-y-2">
                    <ArticleDate
                        className="mr-3.5"
                        date={blogArticle.publishDate}
                        tid={TIDs.blog_article_publication_date}
                    />

                    <div className="flex flex-wrap items-center gap-2 whitespace-nowrap">
                        {blogArticle.blogCategories.map((blogPreviewCategory) => {
                            if (!blogPreviewCategory.parent) {
                                return null;
                            }

                            return (
                                <Flag key={blogPreviewCategory.uuid} href={blogPreviewCategory.link} type="blog">
                                    {blogPreviewCategory.name}
                                </Flag>
                            );
                        })}
                    </div>
                </div>

                {!!blogArticle.text && (
                    <GrapesJsParser text={blogArticle.text} visibleSliderItems={VISIBLE_SLIDER_ITEMS_ARTICLE} />
                )}
            </BlogLayout>

            <LastVisitedProducts />
        </VerticalStack>
    );
};
