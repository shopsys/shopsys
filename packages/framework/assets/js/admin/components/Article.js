import Register from '../../common/utils/Register';

export default class Article {
    constructor($container) {
        this.$typeInputs = $container.find('input[name="article_form[articleData][type]"]');

        this.$typeInputs.on('change', () => {
            this.initTypeVisibility($container);
        });

        this.initTypeVisibility($container);
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
