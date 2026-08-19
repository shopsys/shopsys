import { ArticleDate } from 'components/Basic/ArticleDate/ArticleDate';
import { Flag } from 'components/Basic/Flag/Flag';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { Image } from 'components/Basic/Image/Image';
import { GrapesJsParser } from 'components/Basic/UserText/GrapesJsParser';
import { ARTICLE_INTRODUCTION_ANCHOR_ID } from 'components/Blocks/ArticleAnchorNavigation/ArticleAnchorNavigation';
import { DeferredLastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts';
import { VISIBLE_SLIDER_ITEMS_ARTICLE } from 'components/Blocks/Product/ProductsSlider';
import { BlogLayout } from 'components/Layout/BlogLayout';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { BlogArticleAuthorBox } from 'components/Pages/BlogArticle/BlogArticleAuthorBox';
import { TIDs } from 'cypress/tids';
import { TypeBlogArticleDetailFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleDetailFragment.generated';
import { useMemo } from 'react';
import { getArticleHtmlHeadingAnchors } from 'utils/articleHeadingAnchors';

type BlogArticleDetailContentProps = {
    blogArticle: TypeBlogArticleDetailFragment;
};

export const BlogArticleDetailContent: FC<BlogArticleDetailContentProps> = ({ blogArticle }) => {
    const { headings, htmlWithHeadingAnchors } = useMemo(
        () => getArticleHtmlHeadingAnchors(blogArticle.text ?? ''),
        [blogArticle.text],
    );

    return (
        <VerticalStack gap="md">
            <BlogLayout
                activeCategoryUuid={blogArticle.mainBlogCategoryUuid}
                heading={
                    <h1 id={ARTICLE_INTRODUCTION_ANCHOR_ID} className="scroll-mt-fixed-header">
                        {blogArticle.seoH1 || blogArticle.name}
                    </h1>
                }
                headings={headings}
            >
                {blogArticle.mainImage && (
                    <div className="flex overflow-hidden rounded-xl">
                        <Image
                            priority
                            alt={blogArticle.mainImage.name || blogArticle.name}
                            height={600}
                            sizes="(max-width: 1239px) 100vw, 840px"
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

                    {blogArticle.author && (
                        <div className="mr-3.5 flex items-center gap-2" data-tid={TIDs.blog_article_author}>
                            {blogArticle.author.mainImage ? (
                                <Image
                                    alt=""
                                    className="size-6 rounded-full object-cover"
                                    height={24}
                                    src={blogArticle.author.mainImage.url}
                                    width={24}
                                />
                            ) : (
                                <UserIcon className="size-6 text-text-less" />
                            )}
                            <span className="font-secondary font-semibold text-sm text-text-less">
                                {blogArticle.author.name}
                            </span>
                        </div>
                    )}

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
                    <div data-tid={TIDs.blog_article_content}>
                        <GrapesJsParser
                            text={htmlWithHeadingAnchors}
                            visibleSliderItems={VISIBLE_SLIDER_ITEMS_ARTICLE}
                        />
                    </div>
                )}

                {blogArticle.author && <BlogArticleAuthorBox author={blogArticle.author} />}
            </BlogLayout>

            <DeferredLastVisitedProducts />
        </VerticalStack>
    );
};
