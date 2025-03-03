import { GrapesJs } from 'components/Basic/UserText/GrapesJs';
import { Footer } from 'components/Layout/Footer/Footer';
import { NewsletterForm } from 'components/Layout/Footer/NewsletterForm/NewsletterForm';
import { useFooterArticles } from 'components/Layout/Footer/footerUtils';
import { AutocompleteSearch } from 'components/Layout/Header/AutocompleteSearch/AutocompleteSearch';
import { CartInHeader } from 'components/Layout/Header/Cart/CartInHeader';
import { Logo } from 'components/Layout/Header/Logo/Logo';
import { MenuIconic } from 'components/Layout/Header/MenuIconic/MenuIconic';
import { Navigation } from 'components/Layout/Header/Navigation/Navigation';
import { NotificationBars } from 'components/Layout/NotificationBars/NotificationBars';
import { Webline } from 'components/Layout/Webline/Webline';
import { ArticleTitle } from 'components/Pages/Article/ArticleTitle';
import { useNavigationQuery } from 'graphql/requests/navigation/queries/NavigationQuery.generated';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';

const Index: FC = () => {
    const [{ data: navigationData }] = useNavigationQuery();
    const footerArticles = useFooterArticles();

    return (
        <>
            <NotificationBars />

            <Webline
                className="relative mb-8"
                wrapperClassName="bg-linear-to-tr/srgb from-backgroundBrand to-backgroundBrandLess"
            >
                <div className="flex flex-wrap items-center gap-x-1 gap-y-3 py-3 lg:gap-x-7 lg:pt-6 lg:pb-5">
                    <Logo />

                    <div className="vl:order-2 vl:flex-1 order-6 h-12 w-full transition lg:relative lg:order-4 lg:w-full">
                        <AutocompleteSearch />
                    </div>

                    <div className="order-2 flex">
                        <MenuIconic />
                    </div>

                    <CartInHeader className="vl:order-4 order-3" />
                </div>
                {navigationData?.navigation && <Navigation navigation={navigationData.navigation} />}
            </Webline>

            <Webline>
                <ArticleTitle>Blog or Article title</ArticleTitle>
                <div className="mb-12 flex w-full flex-col">
                    <div className="text-textAccent mb-2 text-left text-xs font-semibold">
                        {new Date().toLocaleDateString() + ''}
                    </div>
                    <GrapesJs className="gjs-editable pt-4 pb-4" />
                </div>
            </Webline>

            <Webline wrapperClassName="bg-backgroundAccentLess">
                <NewsletterForm />
                <Footer footerArticles={footerArticles} />
            </Webline>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default Index;
