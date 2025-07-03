import Register from '../../common/utils/Register';

export default class Article {
    constructor() {
        this.$domainSelectInput = $('#article_form_articleData_domainId');
        this.$metaDescriptionInput = $('#article_form_seo_seoMetaDescription');
        this.$domainSelectInput.on('change', event => {
            this.changeMetaDescriptionPlaceholderByDomainId($(event.target).val());
        });
    }

    changeMetaDescriptionPlaceholderByDomainId(domainId) {
        const metaDescriptionPlaceHolderText = this.$metaDescriptionInput.data(`placeholderDomain${domainId}`);
        this.$metaDescriptionInput.attr('placeholder', metaDescriptionPlaceHolderText);
    }

    static init() {
        // eslint-disable-next-line no-new
        new Article();
    }
}

new Register().registerCallback(Article.init, 'Article.init');
