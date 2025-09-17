import Register from '../../common/utils/Register';

export default class Product {
    static defaultHeaderHeight = 100;

    static init($container) {
        Product.initializeSideNavigation($container);
        Product.initProductVideos($container);
    }

    static initializeSideNavigation($container) {
        const $productDetailNavigation = $container.find('.toc .nav');
        const sections = [];

        $('#product_form h3').each(function () {
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
                    500,
                );
            });
        });

        Product.initIntersectionObserver(sections);
    }

    static initIntersectionObserver(sections) {
        if (sections.length === 0) return;

        let currentActiveSection = null;

        const observer = new IntersectionObserver(
            entries => {
                entries.forEach(entry => {
                    const section = sections.find(s => s.$element[0] === entry.target);
                    if (!section) return;

                    if (entry.isIntersecting) {
                        if (currentActiveSection) {
                            currentActiveSection.$navItem.removeClass('active');
                        }
                        section.$navItem.addClass('active');
                        currentActiveSection = section;
                    }
                });
            },
            {
                rootMargin: `-${Product.defaultHeaderHeight}px 0px -50% 0px`,
                threshold: 0,
            },
        );

        sections.forEach(section => {
            observer.observe(section.$element[0]);
        });
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
