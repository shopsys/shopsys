import $ from 'jquery';
import Register from '../../common/utils/Register';

export default class Product {
    static defaultHeaderHeight = 100;

    static init($container) {
        Product.initializeSideNavigation($container);
        Product.initBackToTopButton();
        Product.initMobileTOC();
        Product.initProductVideos($container);
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

    static adjustFloatingButtonPosition() {
        const $floatingButton = $('.js-mobile-toc-button');

        if ($floatingButton.length === 0) return;

        const $sfToolbar = $('[id^="sfToolbarMainContent-"]');

        if ($sfToolbar.length > 0 && $sfToolbar.is(':visible')) {
            const toolbarHeight = $sfToolbar.outerHeight() || 0;
            $floatingButton.css('bottom', `${toolbarHeight + 70}px`);
        } else {
            $floatingButton.css('bottom', '');
        }
    }

    static initBackToTopButton() {
        const $backToTopButton = $('.js-back-to-top-button');
        const $mobileTocButton = $('.js-mobile-toc-button');

        if ($backToTopButton.length === 0 && $mobileTocButton.length === 0) return;

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

        // Adjust position when Symfony toolbar is toggled
        $(document).on('click', '[id^="sfToolbarHideButton-"], [id^="sfToolbarMiniToggler-"]', () => {
            setTimeout(() => {
                Product.adjustFloatingButtonPosition();
            }, 100);
        });

        // Adjust position on window resize
        $(window).on('resize', () => {
            Product.adjustFloatingButtonPosition();
        });

        // Initial position adjustment
        setTimeout(() => {
            Product.adjustFloatingButtonPosition();
        }, 500);
    }

    static initProductVideos($container) {
        $container.filterAllNodes('.js-videos-collection').on('click', '.js-remove-row', function () {
            $(this).parent().parent().remove();
        });

        $container.filterAllNodes('.js-videos-collection-add-row').on('click', function (_event) {
            const $collection = $(this).closest('.js-form-group').find('.js-videos-collection');
            let index = $collection.data('index');
            index++;
            const prototype = $collection.data('prototype');
            const item = prototype.replace(/__name__label__/g, index).replace(/__name__/g, index);

            const $item = $($.parseHTML(item));

            $item.data('index', index);
            $collection.data('index', index);
            $collection.append($item);
        });
    }
}

new Register().registerCallback(Product.init, 'Product.init');
