import { ArticleTextContent, ArticleTitle, ArticleWrapper } from './ArticleDetail.style';
import UserText from 'components/Helpers/UserText';
import Webline from 'components/Layout/Webline';
import { FC } from 'react';
import { ArticleDetailType } from 'types/article';

type ArticleDetailProps = {
    article: ArticleDetailType;
};

const ArticleDetail: FC<ArticleDetailProps> = (props) => {
    const testIdentifier = 'pages-article-';

    return (
        <Webline data-testid={testIdentifier}>
            <ArticleTitle data-testid={testIdentifier + 'title'}>{props.article.articleName}</ArticleTitle>
            <ArticleWrapper data-testid={testIdentifier + 'content'}>
                {props.article.text !== null ? (
                    <ArticleTextContent>
                        <UserText htmlContent={props.article.text} />
                    </ArticleTextContent>
                ) : null}
            </ArticleWrapper>
        </Webline>
    );
};

export default ArticleDetail;
