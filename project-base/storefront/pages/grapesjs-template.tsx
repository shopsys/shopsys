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
                wrapperClassName="bg-gradient-to-tr from-backgroundBrand to-backgroundBrandLess"
            >
                <div className="flex flex-wrap items-center gap-x-1 gap-y-3 py-3 lg:gap-x-7 lg:pb-5 lg:pt-6">
                    <Logo />

                    <div className="order-6 h-12 w-full transition lg:relative lg:order-4 lg:w-full vl:order-2 vl:flex-1">
                        <AutocompleteSearch />
                    </div>

                    <div className="order-2 flex">
                        <MenuIconic />
                    </div>

                    <CartInHeader className="order-3 vl:order-4" />
                </div>
                {navigationData?.navigation && <Navigation navigation={navigationData.navigation} />}
            </Webline>

            <Webline>
                <ArticleTitle>Blog or Article title</ArticleTitle>
                <div className="mb-12 flex w-full flex-col">
                    <div className="mb-2 text-left text-xs font-semibold text-textAccent">
                        {new Date().toLocaleDateString() + ''}
                    </div>
                    <GrapesJs className="gjs-editable pb-4 pt-4" />
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
