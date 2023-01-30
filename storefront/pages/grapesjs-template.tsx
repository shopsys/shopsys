import { CommonLayout } from '../components/Layout/CommonLayout';
import { GrapesJsStyled } from 'components/Helpers/UserText/UserText.style';
import { Webline } from 'components/Layout/Webline/Webline';
import { ArticleTitle } from 'components/Pages/Article/ArticleDetailContent.style';
import {
    BlogArticleDate,
    BlogArticleTextContent,
    BlogArticleWrapper,
} from 'components/Pages/BlogArticle/BlogArticleDetailContent.style';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { FC } from 'react';
import { nextReduxWrapper } from 'redux/main';

const Index: FC = () => {
    return (
        <CommonLayout title={'Customer'}>
            <Webline>
                <ArticleTitle>Blog or Article title</ArticleTitle>
                <BlogArticleWrapper>
                    <BlogArticleTextContent>
                        <BlogArticleDate>{new Date().toLocaleDateString() + ''}</BlogArticleDate>
                        <GrapesJsStyled
                            className="gjs-editable"
                            data-gjs-type="editable"
                            style={{ paddingTop: 15, paddingBottom: 15 }}
                        ></GrapesJsStyled>
                    </BlogArticleTextContent>
                </BlogArticleWrapper>
            </Webline>
        </CommonLayout>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient(
        (redisClient) => async (context) => initServerSideProps({ context, store, redisClient }),
        store,
    ),
);

export default Index;
