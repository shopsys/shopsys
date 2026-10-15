import { getPublicConfigProperty } from 'envConfig';
import { ArticleDate } from 'components/Basic/ArticleDate/ArticleDate';
import { ContentRenderer } from 'components/Blocks/ContentEditor/ContentRenderer';
import { Footer } from 'components/Layout/Footer/Footer';
import { useFooterArticles } from 'components/Layout/Footer/footerUtils';
import { NewsletterForm } from 'components/Layout/Footer/NewsletterForm/NewsletterForm';
import { AccessibilityNavigation } from 'components/Layout/Header/AccessibilityNavigation/AccessibilityNavigation';
import { AutocompleteSearch } from 'components/Layout/Header/AutocompleteSearch/AutocompleteSearch';
import { CartInHeader } from 'components/Layout/Header/Cart/CartInHeader';
import { Logo } from 'components/Layout/Header/Logo/Logo';
import { MenuIconic } from 'components/Layout/Header/MenuIconic/MenuIconic';
import { Navigation } from 'components/Layout/Header/Navigation/Navigation';
import { NotificationBars } from 'components/Layout/NotificationBars/NotificationBars';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { useNavigationQuery } from 'graphql/requests/navigation/queries/NavigationQuery.generated';
import { useEffect, useState } from 'react';
import { TypeContentDocument } from 'types/contentEditor';
import { useDocumentProducts } from 'utils/contentEditor/useDocumentProducts';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';

const isAllowedOrigin = (origin: string) =>
    origin === window.location.origin || getPublicConfigProperty('contentEditorPreviewOrigins').includes(origin);

const Index: FC = () => {
    const [{ data: navigationData }] = useNavigationQuery();
    const footerArticles = useFooterArticles();
    const [document, setDocument] = useState<TypeContentDocument | null>(null);
    const { products, areProductsFetching } = useDocumentProducts(document);

    useEffect(() => {
        const onMessage = (event: MessageEvent) => {
            if (isAllowedOrigin(event.origin) && event.data?.type === 'content-editor:document') {
                setDocument(event.data.document);
            }
        };

        window.addEventListener('message', onMessage);
        // the administration sends the current document again when it learns that the preview is listening;
        // the message carries no data, so it does not matter which origin the parent has
        if (window.parent !== window) {
            window.parent.postMessage({ type: 'content-editor:ready' }, '*');
        }

        return () => window.removeEventListener('message', onMessage);
    }, []);

    return (
        <div className="flex h-full min-h-screen flex-col" data-tid={TIDs.content_editor_preview_ready}>
            <AccessibilityNavigation />

            <NotificationBars />

            <header>
                <Webline className="relative" wrapperClassName="gjs-template-header-bg">
                    <div className="flex flex-wrap items-center gap-x-1 gap-y-3 py-3 lg:gap-x-7 lg:pt-6 lg:pb-5">
                        <Logo />

                        <div className="order-6 vl:order-2 h-12 w-full vl:flex-1 transition lg:relative lg:order-4 lg:w-full">
                            <AutocompleteSearch />
                        </div>

                        <div className="order-2 flex">
                            <MenuIconic />
                        </div>

                        <CartInHeader className="order-3 vl:order-4" />
                    </div>

                    {navigationData?.navigation && <Navigation navigation={navigationData.navigation} />}
                </Webline>
            </header>

            <main className="mb-10">
                <VerticalStack gap="sm">
                    <Webline className="gjs-template-article-webline" width="vl">
                        <h1>Blog or Article title</h1>
                    </Webline>

                    <Webline className="gjs-template-article-webline" width="vl">
                        <ArticleDate date={new Date().toISOString()} />
                    </Webline>

                    <Webline className="gjs-template-article-webline" width="vl">
                        <ContentRenderer
                            areProductsFetching={areProductsFetching}
                            document={document}
                            products={products}
                        />
                    </Webline>
                </VerticalStack>
            </main>

            <footer className="mt-auto h-fit bg-background-accent-less">
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
