import { COOKIES_STORE_NAME, staticData } from 'fixtures/demodata';
import {
    fetchArticleSlug,
    fetchBlogCategoryLink,
    fetchEntityData,
    fetchEntitySlug,
    normalizeSlug,
} from 'support/entityData';
import { TIDs } from 'tids';

describe('Server-side rendering tests', () => {
    const assertMainContentIsVisibleWithoutJavaScript = (html: string) => {
        const nextContainerIndex = html.indexOf('<div id="__next">');
        expect(nextContainerIndex, 'Expected __next container from Next.js SSR').to.be.greaterThan(-1);

        const mainContentIndex = html.indexOf('<main');
        expect(
            mainContentIndex,
            'Expected <main> in server-rendered HTML (visible without JS via CSS @media (scripting: none) override)',
        ).to.be.greaterThan(-1);
    };

    const assertSsrResponse = (html: string, expectedContentPatterns: (string | RegExp)[]) => {
        // Page should return valid HTML structure
        expect(html).to.contain('</html>');

        // Next.js SSR always includes __NEXT_DATA__ script with serialized page props
        expect(html, 'Expected __NEXT_DATA__ script tag from Next.js SSR').to.contain('id="__NEXT_DATA__"');

        // __next container should NOT be empty (empty = SSR failed, only client shell returned)
        expect(html).to.not.match(/<div id="__next"><\/div>/);

        // Main content must be visible without JavaScript (not only in hidden streamed chunks).
        assertMainContentIsVisibleWithoutJavaScript(html);

        // Check for page-specific content in the server-rendered HTML
        for (const pattern of expectedContentPatterns) {
            if (typeof pattern === 'string') {
                expect(html, `Expected SSR HTML to contain "${pattern}"`).to.contain(pattern);
            } else {
                expect(html, `Expected SSR HTML to match ${pattern}`).to.match(pattern);
            }
        }
    };

    const requestSsrPage = (url: string) => {
        return cy.request({ url, failOnStatusCode: false });
    };

    const visitProductListingWithCookieListView = (entityType: 'brand' | 'flag', uuid: string) => {
        const consoleErrors: string[] = [];

        cy.setCookie(COOKIES_STORE_NAME, encodeURIComponent(JSON.stringify({ productListViewMode: 'list' })), {
            path: '/',
        });

        fetchEntitySlug(entityType, uuid).then((slug) => {
            return requestSsrPage(slug)
                .then((response) => {
                    expect(response.status).to.eq(200);
                    expect(response.body, `Expected ${entityType} SSR to use list view`).to.contain(
                        'class="relative grid grid-cols-1 gap-2"',
                    );
                })
                .then(() =>
                    cy.visit({
                        url: slug,
                        onBeforeLoad(win) {
                            const originalConsoleError = win.console.error;
                            win.console.error = (...args) => {
                                originalConsoleError.apply(win.console, args);
                                consoleErrors.push(args.map(String).join(' '));
                            };
                        },
                    }),
                );
        });

        cy.waitForStableAndInteractiveDOM();
        cy.getByTID([TIDs.blocks_product_list_view_list]).should('have.attr', 'aria-pressed', 'true');
        cy.then(() => {
            const hydrationErrorPattern =
                /hydrat|server(?:-rendered| rendered)? HTML|did not match|Minified React error #(418|421)/i;
            const hydrationErrors = consoleErrors.filter((error) => hydrationErrorPattern.test(error));

            expect(hydrationErrors, `Expected ${entityType} listing to hydrate without errors`).to.deep.equal([]);
        });
    };

    it('[Homepage] should return server-rendered HTML', () => {
        requestSsrPage('/').then((response) => {
            expect(response.status).to.eq(200);
            assertSsrResponse(response.body, [/<title[^>]*>.+<\/title>/, /<h1/]);
        });
    });

    it('[Category Detail] should return server-rendered HTML', () => {
        fetchEntityData('category', staticData.categories.electronics.uuid, 'slug name').then((data) => {
            const slug = normalizeSlug(data.slug);

            return requestSsrPage(slug).then((response) => {
                expect(response.status, `Expected 200 for category slug "${slug}"`).to.eq(200);
                assertSsrResponse(response.body, [data.name, /<title[^>]*>.+<\/title>/]);
            });
        });
    });

    it('[Product Detail] should return server-rendered HTML', () => {
        fetchEntityData('product', staticData.products.helloKitty.uuid, 'slug catalogNumber').then((data) => {
            const slug = normalizeSlug(data.slug);

            return requestSsrPage(slug).then((response) => {
                expect(response.status).to.eq(200);
                assertSsrResponse(response.body, [data.catalogNumber, /<title[^>]*>.+<\/title>/]);
            });
        });
    });

    it('[Blog Article Detail] should return server-rendered HTML', () => {
        fetchEntitySlug('blogArticle', staticData.smokeTestRoutesUuids.blogArticle)
            .then((slug) => requestSsrPage(slug))
            .then((response) => {
                expect(response.status).to.eq(200);
                assertSsrResponse(response.body, [/<title[^>]*>.+<\/title>/, /<h1/]);
            });
    });

    it('[Article Detail] should return server-rendered HTML', () => {
        fetchArticleSlug(staticData.smokeTestRoutesUuids.article)
            .then((slug) => requestSsrPage(slug))
            .then((response) => {
                expect(response.status).to.eq(200);
                assertSsrResponse(response.body, [/<title[^>]*>.+<\/title>/, /<h1/]);
            });
    });

    it('[Brand Detail] should return server-rendered HTML', () => {
        fetchEntitySlug('brand', staticData.smokeTestRoutesUuids.brand)
            .then((slug) => requestSsrPage(slug))
            .then((response) => {
                expect(response.status).to.eq(200);
                assertSsrResponse(response.body, [/<title[^>]*>.+<\/title>/, /<h1/]);
            });
    });

    it('[Brand Detail] should render and hydrate cookie list view without errors', () => {
        visitProductListingWithCookieListView('brand', staticData.smokeTestRoutesUuids.brand);
    });

    it('[Flag Detail] should return server-rendered HTML', () => {
        fetchEntitySlug('flag', staticData.smokeTestRoutesUuids.flag)
            .then((slug) => requestSsrPage(slug))
            .then((response) => {
                expect(response.status).to.eq(200);
                assertSsrResponse(response.body, [/<title[^>]*>.+<\/title>/, /<h1/]);
            });
    });

    it('[Flag Detail] should render and hydrate cookie list view without errors', () => {
        visitProductListingWithCookieListView('flag', staticData.smokeTestRoutesUuids.flag);
    });

    it('[Blog Category] should return server-rendered HTML', () => {
        fetchBlogCategoryLink(staticData.smokeTestRoutesUuids.blogCategory)
            .then((link) => requestSsrPage(link))
            .then((response) => {
                expect(response.status).to.eq(200);
                assertSsrResponse(response.body, [/<title[^>]*>.+<\/title>/, /<h1/]);
            });
    });
});
