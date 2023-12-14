import Register from '../../../common/utils/Register';

export default function validationArticle () {
    const VALIDATION_GROUP_DEFAULT = 'Default';
    const VALIDATION_GROUP_TYPE_SITE = 'typeSite';
    const VALIDATION_GROUP_TYPE_LINK = 'typeLink';
    const TYPE_SITE = 'site';
    const TYPE_LINK = 'link';

    const $articleForm = $('form[name="article_form"]');

    const getCheckedType = function () {
        return $articleForm.find('input[name="article_form[articleData][type]"]:checked').val();
    };

    const initArticleForm = function () {
        let groups = {
            site: [$('#article_form_articleData_text').closest('.form-line'), $('#article_form_seo').closest('.wrap-divider')],
            link: [$('#article_form_articleData_url').closest('.form-line')]
        };

        $.each([].concat.apply(groups.site, groups.link), (index, item) => {
            item.hide();
        });

        $.each(groups[getCheckedType()], (index, item) => {
            item.show();
        });
    };

    $articleForm.find('input[name="article_form[articleData][type]"]').change(initArticleForm);
    initArticleForm();

    $articleForm.jsFormValidator({
        'groups': function () {
            const groups = [VALIDATION_GROUP_DEFAULT];

            const checkedType = getCheckedType();
            if (checkedType === TYPE_SITE) {
                groups.push(VALIDATION_GROUP_TYPE_SITE);
            } else if (checkedType === TYPE_LINK) {
                groups.push(VALIDATION_GROUP_TYPE_LINK);
            }

            return groups;
        }
    });
}

(new Register()).registerCallback(validationArticle);
