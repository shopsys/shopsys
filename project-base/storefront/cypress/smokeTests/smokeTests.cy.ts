import { user } from 'fixtures/demodata';
import { checktHeadlineText } from 'support';
import { TIDs } from 'tids';

type RoutesForSmokeTestsType = {
    skip?: boolean;
    logged?: boolean;
    test?: null | (() => void);
    loginCredentials?: {
        email: string;
        password: string;
    };
    params?: {
        [key: string]: string;
    };
};

context('Smoke tests', () => {
    const { routes } = require('/config/routes');
    const translatedRoutes = routes[0];
    const projectPages = Cypress.env('projectPages');

    const filteredRoutes: Record<string, RoutesForSmokeTestsType> = {
        // static routes
        ['/api/health']: { skip: true },
        ['/customer/change-password']: {
            skip: false,
            logged: true,
            test: () => {
                checktHeadlineText('Change password');
            },
        },
        ['/customer/complaint-detail']: { skip: true }, // TODO add test
        ['/customer/complaints']: {
            skip: false,
            logged: true,
            test: () => {
                checktHeadlineText('My complaints');
            },
        },
        ['/customer/edit-profile']: {
            skip: false,
            logged: true,
            test: () => {
                checktHeadlineText('Edit profile');
            },
        },
        ['/customer/new-complaint']: {
            skip: false,
            logged: true,
            test: () => {
                checktHeadlineText('New complaint');
            },
        },
        ['/customer/order-detail']: { skip: true }, // TODO add test
        ['/customer/orders']: {
            skip: false,
            logged: true,
            test: () => {
                checktHeadlineText('My orders');
            },
        },
        ['/customer/users']: { skip: true },
        ['/order/contact-information']: { skip: true },
        ['/order/payment-status-notify']: { skip: true },
        ['/order/transport-and-payment']: { skip: true },
        ['/order-detail/:urlHash']: { skip: true },
        ['/brands-overview']: {
            skip: false,
            test: () => {
                cy.getByTID([[TIDs.blocks_simplenavigation_, 0]]).should('be.visible');
            },
        },
        ['/cart']: { skip: true },
        ['/contact-form']: {
            skip: false,
            test: () => {
                checktHeadlineText('Write to us');
            },
        },
        ['/grapesjs-template']: {
            skip: false,
            test: () => {
                checktHeadlineText('Blog or Article title');
            },
        },
        ['/']: {
            skip: false,
            test: () => {
                cy.getByTID([TIDs.header]).should('be.visible');
            },
        },
        ['/login']: {
            skip: false,
            test: () => {
                cy.getByTID([TIDs.login_form_submit_button]).should('be.visible');
            },
        },
        ['/new-password']: { skip: true },
        ['/order-confirmation']: { skip: true },
        ['/order-payment-confirmation']: { skip: true },
        ['/personal-data-export']: {
            skip: false,
            test: () => {
                checktHeadlineText('Personal data export');
            },
        },
        ['/personal-data-overview']: {
            skip: false,
            test: () => {
                checktHeadlineText('Personal data overview');
            },
        },
        ['/product-comparison']: {
            skip: false,
            test: () => {
                checktHeadlineText('Product comparison');
            },
        },
        ['/registration']: {
            skip: false,
            test: () => {
                checktHeadlineText('New customer registration');
            },
        },
        ['/reset-password']: {
            skip: false,
            test: () => {
                checktHeadlineText('Forgotten password');
            },
        },
        ['/search']: {
            skip: false,
            params: { ['q']: 'television' },
            test: () => {
                checktHeadlineText('Search results for "television"');
            },
        },
        ['/social-login']: { skip: true },
        ['/stores']: {
            skip: true, // set to false when stores query is fixed
            test: () => {
                checktHeadlineText('Stores');
            },
        },
        ['/styleguide']: { skip: true },
        ['/user-consent']: {
            skip: false,
            test: () => {
                checktHeadlineText('User consent');
            },
        },
        ['/wishlist']: {
            skip: false,
            test: () => {
                checktHeadlineText('Wishlist');
            },
        },

        // dynamic routes
        ['/abandoned-cart/:cartUuid']: { skip: true },
        ['/articles/:articleSlug']: { skip: true },
        ['/blogArticles/:blogArticleSlug']: { skip: true },
        ['/blogCategories/:blogCategorySlug']: { skip: true },
        ['/brands/:brandSlug']: { skip: true },
        ['/categories/:categorySlug']: { skip: true },
        ['/flags/:flagSlug']: { skip: true },
        ['/personal-data-overview/:hash']: { skip: true },
        ['/products/:productSlug']: { skip: true },
        ['/stores/:storeSlug']: { skip: true },

        // custom routes
        ['/electronics']: {
            skip: false,
            logged: false,
            test: () => {
                checktHeadlineText('Electronic devices');
            },
        },
        ['/television-22-sencor-sle-22f46dm4-hello-kitty-plasma']: {
            skip: false,
            logged: false,
            test: () => {
                checktHeadlineText('22" Sencor SLE 22F46DM4 HELLO KITTY plasma');
            },
        },
        ['/about-us']: {
            skip: false,
            logged: false,
            test: () => {
                checktHeadlineText('About us');
            },
        },
        ['/main-blog-page-en']: {
            skip: false,
            logged: false,
            test: () => {
                checktHeadlineText('Main blog page - en');
            },
        },
        ['/blog-article-example-1-en']: {
            skip: false,
            logged: false,
            test: () => {
                checktHeadlineText('Blog article example 1 en - H1');
            },
        },
        ['/apple']: {
            skip: false,
            logged: false,
            test: () => {
                checktHeadlineText('Apple SEO H1');
            },
        },
        ['/action']: {
            skip: false,
            logged: false,
            test: () => {
                checktHeadlineText('Action');
            },
        },
        ['/ostrava']: {
            skip: true, // set to false when stores query is fixed
            logged: false,
            test: () => {
                checktHeadlineText('Ostrava');
            },
        },
    };

    const routesToCheck = Array.from(new Set([...projectPages, ...Object.keys(filteredRoutes)]));

    routesToCheck.forEach((routeName) => {
        const testConfig = filteredRoutes[routeName];

        if (!testConfig) {
            it(`💨 Smoke test - ${routeName}`, () => {
                cy.wrap(null).then(() => {
                    throw new Error(
                        `❗ Missing smoke test configuration for route: ${routeName}. Please add a definition to the filteredRoutes object.`,
                    );
                });
            });
            return;
        }

        const checkRouteCyFn = testConfig?.skip ? it.skip : it;

        checkRouteCyFn(`💨 Smoke test - ${routeName}`, () => {
            const isCustomRoute = routeName in filteredRoutes;

            if (isCustomRoute && filteredRoutes[routeName]?.logged) {
                cy.login(user.email, user.password);
            }

            let routeToRequest = (translatedRoutes[routeName as keyof typeof translatedRoutes] ?? routeName) as string;

            const customParameters = isCustomRoute ? filteredRoutes[routeName]?.params : undefined;

            if (isCustomRoute && customParameters) {
                Object.keys(customParameters).forEach((parameterKey) => {
                    if (routeToRequest.includes(parameterKey)) {
                        routeToRequest = routeToRequest.replace(`:${parameterKey}`, customParameters[parameterKey]);
                    } else {
                        const searchParams = new URLSearchParams(filteredRoutes[routeName].params);
                        routeToRequest = `${routeToRequest}?${searchParams.toString()}`;
                    }
                });
            }

            const consoleErrors: string[] = [];
            const jsErrors: string[] = [];

            // handle uncaught exceptions without failing the test immediately
            cy.on('uncaught:exception', (err) => {
                jsErrors.push(`🔺 Uncaught exception: ${err.message}\n${err.stack || ''}`);
                return false; // prevent the test from failing immediately
            });

            cy.visit({
                url: routeToRequest,
                failOnStatusCode: true,
                onBeforeLoad(win) {
                    // intercept console.error
                    const originalConsoleError = win.console.error;
                    win.console.error = (...args) => {
                        // log the error so we can still see it in test output
                        originalConsoleError.apply(win.console, args);
                        consoleErrors.push(args.join(' '));
                    };

                    // intercept unhandled errors
                    win.addEventListener('error', (e) => {
                        jsErrors.push(
                            `🔻 Unhandled error: ${e.message || '❓ Unknown error'}\n${e.error?.stack || ''}`,
                        );
                    });
                },
            }).then(() => {
                cy.get('#__NEXT_DATA__').should('exist');
                cy.getByTID([TIDs.error_page]).should('not.exist');

                // wait to ensure errors have time to occur
                cy.wait(2000);

                cy.document().then((doc) => {
                    const bodyContent = doc.body.innerText.trim();
                    let errorMessage = '';

                    // for regular pages, we expect no errors
                    try {
                        expect(bodyContent.length, '❌ Page should not be blank').to.be.above(0);

                        if (jsErrors.length > 0 || consoleErrors.length > 0) {
                            errorMessage += `JavaScript errors:\n\n`;

                            if (jsErrors.length > 0) {
                                jsErrors.forEach((err, i) => {
                                    errorMessage += `🟥 ${i + 1}. ${err}\n\n`;
                                });
                            }

                            if (consoleErrors.length > 0) {
                                consoleErrors.forEach((err, i) => {
                                    errorMessage += `🔴 ${i + 1}. ${err}\n\n`;
                                });
                            }

                            assert.fail(errorMessage);
                        }
                    } catch (err) {
                        // if the page is blank and we have JS errors, provide a simple error message
                        if (bodyContent.length === 0 && (jsErrors.length > 0 || consoleErrors.length > 0)) {
                            let errorMessage = `Blank page with JavaScript errors:\n\n`;

                            if (jsErrors.length > 0) {
                                jsErrors.forEach((err, i) => {
                                    errorMessage += `🟥 ${i + 1}. ${err}\n\n`;
                                });
                            }

                            if (consoleErrors.length > 0) {
                                consoleErrors.forEach((err, i) => {
                                    errorMessage += `🔴 ${i + 1}. ${err}\n\n`;
                                });
                            }

                            assert.fail(errorMessage);
                        } else {
                            // if it's just a blank page without JS errors, rethrow the original error
                            throw err;
                        }
                    }

                    if (isCustomRoute && filteredRoutes[routeName]?.test) {
                        filteredRoutes[routeName].test?.();
                    }
                });
            });
        });
    });
});
