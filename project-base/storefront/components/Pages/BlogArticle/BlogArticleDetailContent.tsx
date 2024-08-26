import { Image } from 'components/Basic/Image/Image';
import { GrapesJsParser } from 'components/Basic/UserText/GrapesJsParser';
import { BlogLayout } from 'components/Layout/BlogLayout';
import { ArticleTitle } from 'components/Pages/Article/ArticleTitle';
import { TIDs } from 'cypress/tids';
import { TypeBlogArticleDetailFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleDetailFragment.generated';
import { useFormatDate } from 'utils/formatting/useFormatDate';

type BlogArticleDetailContentProps = {
    blogArticle: TypeBlogArticleDetailFragment;
};

export const BlogArticleDetailContent: FC<BlogArticleDetailContentProps> = ({ blogArticle }) => {
    const { formatDate } = useFormatDate();

    return (
        <BlogLayout activeCategoryUuid={blogArticle.mainBlogCategoryUuid}>
            <ArticleTitle>{blogArticle.seoH1 || blogArticle.name}</ArticleTitle>
            <div className="px-5">
                <div className="mb-12 flex flex-col">
                    {blogArticle.mainImage && (
                        <div className="mb-10 flex overflow-hidden">
                            <Image
                                priority
                                alt={blogArticle.mainImage.name || blogArticle.name}
                                height={600}
                                src={blogArticle.mainImage.url}
                                width={1280}
                            />
                        </div>
                    )}

                    <div
                        className="mb-2 text-left text-xs font-semibold text-textDisabled"
                        tid={TIDs.blog_article_publication_date}
                    >
                        {formatDate(blogArticle.publishDate, 'l')}
                    </div>

                    {!!blogArticle.text && <GrapesJsParser isBlogPage text={blogArticle.text} />}
                </div>
            </div>
        </BlogLayout>
    );
};
