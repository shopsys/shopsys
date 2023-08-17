import { ArticleTitle } from './ArticleTitle';
import { GrapesJsParser } from 'components/Basic/UserText/GrapeJsParser';
import { Webline } from 'components/Layout/Webline/Webline';
import { useFormatDate } from 'hooks/formatting/useFormatDate';
import { ArticleDetailFragmentApi } from 'graphql/requests/articlesInterface/articles/fragments/ArticleDetailFragment.generated';

type ArticleDetailContentProps = {
    article: ArticleDetailFragmentApi;
};

const TEST_IDENTIFIER = 'pages-article-';

export const ArticleDetailContent: FC<ArticleDetailContentProps> = ({ article }) => {
    const { formatDate } = useFormatDate();

    return (
        <Webline dataTestId={TEST_IDENTIFIER}>
            <ArticleTitle dataTestId={TEST_IDENTIFIER + 'title'}>{article.articleName}</ArticleTitle>
            <p className="mb-2 px-5 text-left text-xs font-semibold text-grey">{formatDate(article.createdAt, 'l')}</p>
            <div className="px-5 lg:flex" data-testid={TEST_IDENTIFIER + 'content'}>
                {article.text !== null && (
                    <div className="order-2 mb-16 flex w-full flex-col">
                        <GrapesJsParser text={article.text} />
                    </div>
                )}
            </div>
        </Webline>
    );
};
