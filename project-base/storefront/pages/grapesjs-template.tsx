import { CommonLayout } from '../components/Layout/CommonLayout';
import { GrapesJs } from 'components/Helpers/UserText/GrapesJs';
import { Webline } from 'components/Layout/Webline/Webline';
import { ArticleTitle } from 'components/Pages/Article/ArticleTitle';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';

const Index: FC = () => (
    <CommonLayout title="Customer">
        <Webline>
            <ArticleTitle dataTestId="">Blog or Article title</ArticleTitle>
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

export const getServerSideProps = getServerSidePropsWithRedisClient(
    (redisClient) => async (context) => initServerSideProps({ context, redisClient }),
);

export default Index;
