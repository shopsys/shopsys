import { Image } from 'components/Basic/Image/Image';
import { GrapeJsParser } from 'components/Helpers/GrapeJsParser';
import { Webline } from 'components/Layout/Webline/Webline';
import { ArticleTitle } from 'components/Pages/Article/ArticleTitle';
import { BlogArticleDetailFragmentApi } from 'graphql/generated';
import { formatDate } from 'helpers/formaters/formatDate';
import { getFirstImageOrNull } from 'helpers/mappers/image';

type BlogArticleDetailContentProps = {
    blogArticle: BlogArticleDetailFragmentApi;
};

const TEST_IDENTIFIER = 'pages-blogarticle-';

export const BlogArticleDetailContent: FC<BlogArticleDetailContentProps> = ({ blogArticle }) => (
    <Webline>
        <ArticleTitle dataTestId={TEST_IDENTIFIER + 'title'}>{blogArticle.name}</ArticleTitle>
        <div className="px-5">
            <div className="mb-12 flex w-full flex-col">
                {getFirstImageOrNull(blogArticle.images) !== null && (
                    <div className="mb-10 flex overflow-hidden" data-testid={TEST_IDENTIFIER + 'image'}>
                        <Image image={getFirstImageOrNull(blogArticle.images)} type="default" alt={blogArticle.name} />
                    </div>
                )}
                <div className="mb-2 text-left text-xs font-semibold text-grey" data-testid={TEST_IDENTIFIER + 'date'}>
                    {formatDate(blogArticle.publishDate, 'l')}
                </div>
                {blogArticle.text !== null && (
                    <GrapeJsParser text={blogArticle.text} allProducts={blogArticle.blogArticleProducts} />
                )}
            </div>
        </div>
    </Webline>
);
