import { ArticleTitle } from './ArticleTitle';
import { GrapesJsParser } from 'components/Basic/UserText/GrapesJsParser';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeArticleDetailFragment } from 'graphql/requests/articlesInterface/articles/fragments/ArticleDetailFragment.generated';
import { useFormatDate } from 'utils/formatting/useFormatDate';

type ArticleDetailContentProps = {
    article: TypeArticleDetailFragment;
};

export const ArticleDetailContent: FC<ArticleDetailContentProps> = ({ article }) => {
    const { formatDate } = useFormatDate();

    return (
        <Webline>
            <ArticleTitle>{article.articleName}</ArticleTitle>
            <p className="mb-2 px-5 text-left text-xs font-semibold text-grey">{formatDate(article.createdAt, 'l')}</p>
            <div className="px-5 lg:flex">
                {article.text !== null && (
                    <div className="order-2 mb-16 flex w-full flex-col">
                        <GrapesJsParser text={article.text} />
                    </div>
                )}
            </div>
        </Webline>
    );
};
