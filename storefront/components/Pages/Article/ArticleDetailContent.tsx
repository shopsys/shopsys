import { ArticleTitle } from './ArticleTitle';
import { UserText } from 'components/Helpers/UserText/UserText';
import { Webline } from 'components/Layout/Webline/Webline';
import { formatDate } from 'helpers/formaters/formatDate';
import { FC } from 'react';
import { ArticleDetailType } from 'types/article';

type ArticleDetailContentProps = {
    article: ArticleDetailType;
};

const TEST_IDENTIFIER = 'pages-article-';

export const ArticleDetailContent: FC<ArticleDetailContentProps> = ({ article }) => (
    <Webline testIdentifier={TEST_IDENTIFIER}>
        <ArticleTitle dataTestId={TEST_IDENTIFIER + 'title'}>{article.articleName}</ArticleTitle>
        <p className="mb-2 px-5 text-left text-xs font-semibold text-grey">{formatDate(article.createdAt, 'l')}</p>
        <div className="px-5 lg:flex" data-testid={TEST_IDENTIFIER + 'content'}>
            {article.text !== null && (
                <div className="order-2 mb-16 flex w-full flex-col">
                    <UserText htmlContent={article.text} />
                </div>
            )}
        </div>
    </Webline>
);
