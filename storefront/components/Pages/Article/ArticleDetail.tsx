import { ArticleTextContent, ArticleTitle, ArticleWrapper } from './ArticleDetail.style';
import { ArticleDetailType } from 'connectors/article/types';
import { FC } from 'react';
import UserText from 'components/Helpers/UserText';
import Webline from 'components/Layout/Webline';

type ArticleDetailProps = {
    article: ArticleDetailType;
};

const ArticleDetail: FC<ArticleDetailProps> = (props) => {
    return (
        <Webline>
            <ArticleTitle>{props.article.articleName}</ArticleTitle>
            <ArticleWrapper>
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
