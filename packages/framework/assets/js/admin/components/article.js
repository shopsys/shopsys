import Register from '../../common/utils/register';

export default class Article {

    constructor () {
        this.$domainSelectInput = $('#article_form_articleData_domainId');
        this.$metaDescriptionInput = $('#article_form_seo_seoMetaDescription');

        const _this = this;
        this.$domainSelectInput.on('change', event => {
            _this.changeMetaDescriptionPlaceholderByDomainId($(event.target).val());
        });
    }

    changeMetaDescriptionPlaceholderByDomainId (domainId) {
        const metaDescriptionPlaceHolderText = this.$metaDescriptionInput.data('placeholderDomain' + domainId);
        this.$metaDescriptionInput.attr('placeholder', metaDescriptionPlaceHolderText);
    }

    static init () {
        // eslint-disable-next-line no-new
        new Article();
    }
}

(new Register()).registerCallback(Article.init);
