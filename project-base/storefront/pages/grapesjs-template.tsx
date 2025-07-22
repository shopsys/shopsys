import { ArticleDate } from 'components/Basic/ArticleDate/ArticleDate';
import { GrapesJs } from 'components/Basic/UserText/GrapesJs';
import { Footer } from 'components/Layout/Footer/Footer';
import { NewsletterForm } from 'components/Layout/Footer/NewsletterForm/NewsletterForm';
import { useFooterArticles } from 'components/Layout/Footer/footerUtils';
import { AccessibilityNavigation } from 'components/Layout/Header/AccessibilityNavigation/AccessibilityNavigation';
import { AutocompleteSearch } from 'components/Layout/Header/AutocompleteSearch/AutocompleteSearch';
import { CartInHeader } from 'components/Layout/Header/Cart/CartInHeader';
import { Logo } from 'components/Layout/Header/Logo/Logo';
import { MenuIconic } from 'components/Layout/Header/MenuIconic/MenuIconic';
import { Navigation } from 'components/Layout/Header/Navigation/Navigation';
import { NotificationBars } from 'components/Layout/NotificationBars/NotificationBars';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { useNavigationQuery } from 'graphql/requests/navigation/queries/NavigationQuery.generated';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';

const Index: FC = () => {
    const [{ data: navigationData }] = useNavigationQuery();
    const footerArticles = useFooterArticles();

    return (
        <div className="flex h-full min-h-screen flex-col">
            <AccessibilityNavigation />

            <NotificationBars />

            <header>
                <Webline className="relative" wrapperClassName="gjs-template-header-bg">
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
            </header>

            <main className="mt-4 mb-10">
                <VerticalStack gap="sm">
                    <Webline className="gjs-template-article-webline" width="md">
                        <h1>Blog or Article title</h1>
                    </Webline>

                    <Webline className="gjs-template-article-webline" width="md">
                        <ArticleDate date={new Date().toISOString()} />
                    </Webline>

                    <Webline className="gjs-template-article-webline" width="md">
                        <GrapesJs className="gjs-editable min-h-6" />
                    </Webline>
                </VerticalStack>
            </main>

            <footer className="bg-background-accent-less mt-auto h-fit">
                <NewsletterForm />
                <Footer footerArticles={footerArticles} />
            </footer>
        </div>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default Index;
