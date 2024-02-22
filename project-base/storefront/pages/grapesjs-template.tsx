import { GrapesJs } from 'components/Basic/UserText/GrapesJs';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { ArticleTitle } from 'components/Pages/Article/ArticleTitle';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';

const Index: FC = () => (
    <CommonLayout title="Customer">
        <Webline>
            <ArticleTitle>Blog or Article title</ArticleTitle>
            <div className="px-5">
                <div className="mb-12 flex w-full flex-col">
                    <div className="mb-2 text-left text-xs font-semibold text-grey">
                        {new Date().toLocaleDateString() + ''}
                    </div>
                    <GrapesJs className="gjs-editable pt-4 pb-4" />
                </div>
            </div>
        </Webline>
    </CommonLayout>
);

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default Index;
