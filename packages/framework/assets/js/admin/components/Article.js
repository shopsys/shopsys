import $ from 'jquery';
import Register from '../../common/utils/Register';

export default class Article {
    constructor($container) {
        this.$domainSelectInput = $container.find('#article_form_articleData_domainId');
        this.$metaDescriptionInput = $container.find('#article_form_seo_seoMetaDescription');
        this.$typeInputs = $container.find('input[name="article_form[articleData][type]"]');

        this.$domainSelectInput.on('change', event => {
            this.changeMetaDescriptionPlaceholderByDomainId($(event.target).val());
        });

        this.$typeInputs.on('change', () => {
            this.initTypeVisibility($container);
        });

        this.initTypeVisibility($container);
    }

    changeMetaDescriptionPlaceholderByDomainId(domainId) {
        const metaDescriptionPlaceHolderText = this.$metaDescriptionInput.data(`placeholderDomain${domainId}`);
        this.$metaDescriptionInput.attr('placeholder', metaDescriptionPlaceHolderText);
    }

    initTypeVisibility($container) {
        const checkedType = this.$typeInputs.filter(':checked').val();

        $container.find('[data-js-article-type-content]').hide();
        if (checkedType) {
            $container.find(`[data-js-article-type-content="${checkedType}"]`).show();
        }
    }

    static init($container) {
        const $articleForm = $container.filterAllNodes('form[name="article_form"]');

        if ($articleForm.length > 0) {
            // eslint-disable-next-line no-new
            new Article($articleForm);
        }
    }
}

new Register().registerCallback(Article.init, 'Article.init');
