import grapesjs from 'grapesjs';
import Translator from 'bazinga-translator';

export default grapesjs.plugins.add('products', editor => {
    const dataProduct = 'data-product';
    const dataProducts = 'data-products';
    const dataProductName = 'data-product-name';
    const dataProductsEvent = 'change:attributes:data-products';

    const createProductsView = (component, model) => {
        component.listenTo(model, dataProductsEvent, component.render);
    };

    const updateProductsModel = (element, productsCatnumString) => {
        element.empty();
        const components = element.components();

        if (productsCatnumString) {
            const response = $.get({
                url: `${window.location.origin}/admin/product/names-by-catnums/${productsCatnumString}`,
                async: false
            });

            const productNames = response.status === 200 ? response.responseJSON : undefined;

            for (const product of productsCatnumString.split(',')) {
                if (productNames[product] !== undefined) {
                    components.add('<div class="gjs-product"></div>').addAttributes({
                        [dataProduct]: product,
                        [dataProductName]: productNames[product]
                    });
                }
            }
        }
    };

    const renderProductsView = (element, model) => {
        if (!model.getAttributes()[dataProducts]) {
            element.appendChild(createDiv(Translator.trans(
                'No products are set. Please add some in the component settings.'
            )));
        }
    };

    const updateProduct = parent => {
        if (parent.is('products')) {
            parent.addAttributes({
                [dataProducts]: parent.components().map(component => component.getAttributes()[dataProduct]).join(',')
            });
        }
    };

    const removeProduct = component => {
        const product = component.getAttributes()[dataProduct];
        const parent = component.parent();

        if (parent) {
            const products = parent.getAttributes()[dataProducts];

            if (products) {
                parent.addAttributes({
                    [dataProducts]: products.split(',').filter(innerProduct => innerProduct !== product).join(',')
                });
            }
        }
    };

    const renderProductView = (element, model) => {
        const attributes = model.getAttributes();
        const product = attributes[dataProduct];
        const productName = attributes[dataProductName];

        if (product && productName) {
            element.appendChild(createDiv(`
                <div style="margin-top: 2em; text-align: center;">${productName}<br/><br/>${product}</div>
                <img src="${window.location.origin}/public/admin/images/grapejs-product-list-item.png" alt="${dataProductName}" style="max-width: 100%;/>
            `));
        }
    };

    const createDiv = content => {
        const div = document.createElement('div');
        div.innerHTML = content;
        return div;
    };

    editor.on('component:drag:end', ({ parent }) => updateProduct(parent));

    editor.Blocks.add('products', {
        id: 'products',
        label: Translator.trans('List of products'),
        category: 'Basic',
        media: '<svg style="width:48px;height:48px" viewBox="0 0 24 24"><path fill="currentColor" d="M8.5,13.5L11,16.5L14.5,12L19,18H5M21,19V5C21,3.89 20.1,3 19,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19Z" /></svg>',
        content: {
            type: 'products'
        }
    });

    editor.DomComponents.addType('product', {
        isComponent: element => element.classList && element.classList.contains('gjs-product'),
        model: {
            defaults: {
                copyable: false,
                droppable: false,
                draggable: 'div.gjs-products',
                attributes: {
                    class: 'gjs-product',
                    [dataProduct]: undefined,
                    [dataProductName]: undefined
                }
            },
            removed () {
                removeProduct(this);
            }
        },
        view: {
            onRender: ({ el, model }) => renderProductView(el, model)
        }
    });

    editor.DomComponents.addType('products', {
        isComponent: element => element.classList && element.classList.contains('gjs-products'),
        model: {
            init () {
                updateProductsModel(this, this.getAttributes()[dataProducts]);

                this.on(dataProductsEvent, (element, products) => {
                    updateProductsModel(element, products);
                });
            },
            defaults: {
                droppable: (component, destination) => component.parent() === destination,
                attributes: {
                    class: 'gjs-products'
                },
                styles: `
                    .gjs-products { text-align: center; }
                    .gjs-products .gjs-product { display: inline-block; width: 20%; margin: 1em; }
                `,
                traits: [
                    {
                        type: 'text',
                        name: dataProducts,
                        label: Translator.trans('Catalog numbers delimited by comma')
                    }
                ]
            }
        },
        view: {
            init ({ model }) {
                createProductsView(this, model);
            },
            onRender: ({ el, model }) => renderProductsView(el, model)
        }
    });
});
