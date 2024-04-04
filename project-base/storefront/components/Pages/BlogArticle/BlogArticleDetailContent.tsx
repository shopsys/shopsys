import { Image } from 'components/Basic/Image/Image';
import { GrapesJsParser } from 'components/Basic/UserText/GrapesJsParser';
import { Webline } from 'components/Layout/Webline/Webline';
import { ArticleTitle } from 'components/Pages/Article/ArticleTitle';
import { TypeBlogArticleDetailFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleDetailFragment.generated';
import { useFormatDate } from 'utils/formatting/useFormatDate';

type BlogArticleDetailContentProps = {
    blogArticle: TypeBlogArticleDetailFragment;
};

export const BlogArticleDetailContent: FC<BlogArticleDetailContentProps> = ({ blogArticle }) => {
    const { formatDate } = useFormatDate();

    return (
        <Webline>
            <ArticleTitle>{blogArticle.seoH1 || blogArticle.name}</ArticleTitle>
            <div className="px-5">
                <div className="mb-12 flex w-full flex-col">
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

                    <div className="mb-2 text-left text-xs font-semibold text-grey">
                        {formatDate(blogArticle.publishDate, 'l')}
                    </div>

                    {!!blogArticle.text && <GrapesJsParser text={blogArticle.text} />}
                </div>
            </div>
        </Webline>
    );
};
