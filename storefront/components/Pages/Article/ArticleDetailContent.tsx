import { ArticleDate, ArticleTextContent, ArticleTitle, ArticleWrapper } from './ArticleDetailContent.style';
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
        <ArticleTitle data-testid={TEST_IDENTIFIER + 'title'}>{article.articleName}</ArticleTitle>
        <ArticleDate>{formatDate(article.createdAt, 'l')}</ArticleDate>
        <ArticleWrapper data-testid={TEST_IDENTIFIER + 'content'}>
            {article.text !== null && (
                <ArticleTextContent>
                    <UserText htmlContent={article.text} />
                </ArticleTextContent>
            )}
        </ArticleWrapper>
    </Webline>
);
