import { Image } from 'components/Basic/Image/Image';
import { GrapesJsParser } from 'components/Basic/UserText/GrapesJsParser';
import { Webline } from 'components/Layout/Webline/Webline';
import { ArticleTitle } from 'components/Pages/Article/ArticleTitle';
import { BlogArticleDetailFragmentApi } from 'graphql/generated';
import { useFormatDate } from 'hooks/formatting/useFormatDate';

type BlogArticleDetailContentProps = {
    blogArticle: BlogArticleDetailFragmentApi;
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
                                image={blogArticle.mainImage}
                                type="default"
                                alt={blogArticle.mainImage.name || blogArticle.name}
                            />
                        </div>
                    )}

                    <div className="mb-2 text-left text-xs font-semibold text-grey">
                        {formatDate(blogArticle.publishDate, 'l')}
                    </div>

                    {!!blogArticle.text && <GrapesJsParser text={blogArticle.text} uuid={blogArticle.uuid} />}
                </div>
            </div>
        </Webline>
    );
};
