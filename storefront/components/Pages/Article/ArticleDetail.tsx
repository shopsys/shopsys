import { ArticleDate, ArticleTextContent, ArticleTitle, ArticleWrapper } from './ArticleDetail.style';
import UserText from 'components/Helpers/UserText';
import Webline from 'components/Layout/Webline';
import { formatDate } from 'helpers/formaters/formatDate';
import { FC } from 'react';
import { ArticleDetailType } from 'types/article';

type ArticleDetailProps = {
    article: ArticleDetailType;
};

const TEST_IDENTIFIER = 'pages-article-';

const ArticleDetail: FC<ArticleDetailProps> = ({ article }) => {
    return (
        <Webline data-testid={TEST_IDENTIFIER}>
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
};

export default ArticleDetail;
