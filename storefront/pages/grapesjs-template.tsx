import { CommonLayout } from '../components/Layout/CommonLayout';
import { GrapesJsStyled } from 'components/Helpers/UserText/UserText.style';
import { Webline } from 'components/Layout/Webline/Webline';
import { ArticleTitle } from 'components/Pages/Article/ArticleTitle';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { FC } from 'react';
import { nextReduxWrapper } from 'redux/main';

const Index: FC = () => (
    <CommonLayout title="Customer">
        <Webline>
            <ArticleTitle dataTestId="">Blog or Article title</ArticleTitle>
            <div className="px-5">
                <div className="mb-12 flex w-full flex-col">
                    <div className="mb-2 text-left text-xs font-semibold text-grey">
                        {new Date().toLocaleDateString() + ''}
                    </div>
                    <GrapesJsStyled
                        className="gjs-editable"
                        data-gjs-type="editable"
                        style={{ paddingTop: 15, paddingBottom: 15 }}
                    ></GrapesJsStyled>
                </div>
            </div>
        </Webline>
    </CommonLayout>
);

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient(
        (redisClient) => async (context) => initServerSideProps({ context, store, redisClient }),
        store,
    ),
);

export default Index;
