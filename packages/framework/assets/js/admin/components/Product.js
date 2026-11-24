import $ from 'jquery';
import Register from '../../common/utils/Register';

export default class Product {
    static defaultHeaderHeight = 100;

    static floatingButtonGapAboveActionBar = 16;

    static init($container) {
        Product.initializeSideNavigation($container);
        Product.initBackToTopButton();
        Product.initMobileTOC();
        Product.initFloatingButtonPosition($container);
    }

    static initializeSideNavigation($container) {
        const $productDetailNavigation = $container.find('.toc .nav');
        const sections = [];

        $('#product_form h3.card-title').each(function () {
            const $title = $(this);

            const $navigationItem = $(`<span class="nav-link cursor-pointer">${$title.text()}</span>`);
            $productDetailNavigation.append($navigationItem);

            sections.push({
                $element: $title,
                $navItem: $navigationItem,
            });

            $navigationItem.click(() => {
                const headerHeight = $('.page-header').outerHeight() || Product.defaultHeaderHeight;
                const targetTop = $title.offset().top - headerHeight - 50;

                $('html, body').animate(
                    {
                        scrollTop: targetTop,
                    },
                    300,
                );
            });
        });

        Product.initIntersectionObserver(sections);
    }

    static initIntersectionObserver(sections) {
        if (sections.length === 0) return;

        let currentActiveSection = null;

        const observer = new IntersectionObserver(
            () => {
                const visibleSectionsInViewport = sections.filter(section => {
                    const rect = section.$element[0].getBoundingClientRect();

                    return rect.top < window.innerHeight && rect.bottom > Product.defaultHeaderHeight;
                });

                const closestToTopSection = visibleSectionsInViewport.reduce(
                    (closest, section) => {
                        const rect = section.$element[0].getBoundingClientRect();
                        const distance = Math.abs(rect.top - Product.defaultHeaderHeight);

                        return distance < closest.distance ? { section, distance } : closest;
                    },
                    { section: null, distance: Infinity },
                ).section;

                if (closestToTopSection && closestToTopSection !== currentActiveSection) {
                    if (currentActiveSection) {
                        currentActiveSection.$navItem.removeClass('active');
                    }

                    closestToTopSection.$navItem.addClass('active');
                    currentActiveSection = closestToTopSection;
                }
            },
            {
                rootMargin: `-${Product.defaultHeaderHeight}px 0px -25% 0px`,
                threshold: 0,
            },
        );

        sections.forEach(section => {
            observer.observe(section.$element[0]);
        });
    }

    static initMobileTOC() {
        const $mobileButton = $('.js-mobile-toc-button');
        const $mobileToc = $('#mobile-toc');
        const $desktopToc = $('#toc');
        const $offcanvas = $('#mobile-toc-menu');

        if ($mobileButton.length === 0 || $mobileToc.length === 0) return;

        $offcanvas.on('show.bs.offcanvas', () => {
            const $clonedItems = $desktopToc.children().clone(true, true);

            $mobileToc.empty().append($clonedItems);

            $mobileToc.find('.nav-link').on('click', _e => {
                const $closeButton = $offcanvas.find('.btn-close');
                if ($closeButton.length > 0) {
                    $closeButton[0].click();
                }
            });
        });
    }

    static initFloatingButtonPosition($container) {
        const isFloatingButtonInContainer = $container.filterAllNodes('.js-mobile-toc-button').length > 0;
        const actionBar = document.querySelector('.js-action-bar-bottom');

        if (!isFloatingButtonInContainer || actionBar === null) return;

        const adjustFloatingButtonPositionAfterActionBarMove = event => {
            if (event.target === actionBar) {
                Product.adjustFloatingButtonPosition();
            }
        };

        // The action bar wraps into more rows on narrow screens and grows when the unsaved changes info appears
        new ResizeObserver(() => Product.adjustFloatingButtonPosition()).observe(actionBar);
        // The action bar slides above the Symfony toolbar with a transition in the dev environment
        actionBar.addEventListener('transitionend', adjustFloatingButtonPositionAfterActionBarMove);
    }

    static adjustFloatingButtonPosition() {
        const floatingButton = document.querySelector('.js-mobile-toc-button');
        const actionBar = document.querySelector('.js-action-bar-bottom');

        if (floatingButton === null) return;

        if (actionBar === null) {
            floatingButton.style.bottom = '';

            return;
        }

        const actionBarBottom = parseFloat(window.getComputedStyle(actionBar).bottom) || 0;
        const actionBarTop = actionBarBottom + actionBar.offsetHeight;

        floatingButton.style.bottom = `${actionBarTop + Product.floatingButtonGapAboveActionBar}px`;
    }

    static initBackToTopButton() {
        const $backToTopButton = $('.js-back-to-top-button');

        if ($backToTopButton.length === 0) return;

        const headerHeight = Product.defaultHeaderHeight;

        $backToTopButton.on('click', () => {
            $('html, body').animate(
                {
                    scrollTop: 0,
                },
                300,
            );

            const $closeButton = $('#mobile-toc-menu').find('.btn-close');

            if ($closeButton.length > 0) {
                $closeButton[0].click();
            }
        });

        $(window).on('scroll', () => {
            const scrollTop = $(window).scrollTop();

            if (scrollTop > headerHeight + 100) {
                $backToTopButton.removeClass('d-none');
            } else {
                $backToTopButton.addClass('d-none');
            }
        });
    }
}

new Register().registerCallback(Product.init, 'Product.init');
