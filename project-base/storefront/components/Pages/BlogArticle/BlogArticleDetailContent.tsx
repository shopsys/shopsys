import { ArticleDate } from 'components/Basic/ArticleDate/ArticleDate';
import { Flag } from 'components/Basic/Flag/Flag';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { Image } from 'components/Basic/Image/Image';
import { ContentRenderer } from 'components/Blocks/ContentEditor/ContentRenderer';
import { DeferredLastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts';
import { BlogLayout } from 'components/Layout/BlogLayout';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { BlogArticleAuthorBox } from 'components/Pages/BlogArticle/BlogArticleAuthorBox';
import { TIDs } from 'cypress/tids';
import { TypeBlogArticleDetailFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleDetailFragment.generated';
import { useMemo } from 'react';
import { ARTICLE_INTRODUCTION_ANCHOR_ID } from 'utils/articleHeadingAnchors';
import { addHeadingAnchors } from 'utils/contentEditor/addHeadingAnchors';
import { toContentDocument } from 'utils/contentEditor/toContentDocument';
import { useDocumentProducts } from 'utils/contentEditor/useDocumentProducts';
import { getImageAlt } from 'utils/imageAltText';

type BlogArticleDetailContentProps = {
    blogArticle: TypeBlogArticleDetailFragment;
};

export const BlogArticleDetailContent: FC<BlogArticleDetailContentProps> = ({ blogArticle }) => {
    const { document, headings } = useMemo(
        () => addHeadingAnchors(toContentDocument(blogArticle.content)),
        [blogArticle.content],
    );
    const { products, areProductsFetching } = useDocumentProducts(document);

    return (
        <VerticalStack gap="md">
            <BlogLayout
                activeCategoryUuid={blogArticle.mainBlogCategoryUuid}
                heading={
                    <h1 id={ARTICLE_INTRODUCTION_ANCHOR_ID} className="scroll-mt-fixed-header">
                        {blogArticle.seo.h1 || blogArticle.name}
                    </h1>
                }
                headings={headings}
            >
                {blogArticle.mainImage && (
                    <div className="flex overflow-hidden rounded-xl">
                        <Image
                            priority
                            alt={getImageAlt(blogArticle.mainImage.name, blogArticle.name)}
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
                                    alt={getImageAlt(blogArticle.author.mainImage.name, blogArticle.author.name)}
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

                {document && (
                    <div data-tid={TIDs.blog_article_content}>
                        <ContentRenderer
                            areProductsFetching={areProductsFetching}
                            document={document}
                            products={products}
                        />
                    </div>
                )}

                {blogArticle.author && <BlogArticleAuthorBox author={blogArticle.author} />}
            </BlogLayout>

            <DeferredLastVisitedProducts />
        </VerticalStack>
    );
};
