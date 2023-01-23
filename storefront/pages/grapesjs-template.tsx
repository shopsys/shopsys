import {
    BlogArticleDate,
    BlogArticleTextContent,
    BlogArticleWrapper,
} from 'components/Pages/BlogArticle/BlogArticleDetailContent.style';
import { FC } from 'react';
import { Webline } from 'components/Layout/Webline/Webline';
import { ArticleTitle } from 'components/Pages/Article/ArticleDetailContent.style';
import { UserTextStyled } from 'components/Helpers/UserText/UserText.style';
import { nextReduxWrapper } from 'redux/main';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';

const Index: FC = () => {
    return (
        <Webline>
            <ArticleTitle>Blog title</ArticleTitle>
            <BlogArticleWrapper>
                <BlogArticleTextContent>
                    <BlogArticleDate>{new Date().toLocaleDateString() + ''}</BlogArticleDate>
                    <UserTextStyled className="gjs-editable" data-gjs-type="editable" />
                </BlogArticleTextContent>
            </BlogArticleWrapper>
        </Webline>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient(
        (redisClient) => async (context) => initServerSideProps({ context, store, redisClient }),
        store,
    ),
);


export default Index;
