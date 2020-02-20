import Register from 'framework/common/utils/register';

export default function validationArticle () {
    const VALIDATION_GROUP_TYPE_SITE = 'typeSite';
    const VALIDATION_GROUP_TYPE_LINK = 'typeLink';
    const TYPE_SITE = 'site';
    const TYPE_LINK = 'link';

    const $articleForm = $('form[name="article_form"]');

    const getCheckedType = function () {
        return $articleForm.find('input[name="article_form[articleData][type]"]:checked').val();
    };

    const initArticleForm = function () {
        let siteGroup = [$('#article_form_articleData_text').closest('.form-line'), $('#article_form_seo').closest('.wrap-divider')];
        let linkGroup = [$('#article_form_articleData_url').closest('.form-line')];

        $.each($.extend([], siteGroup, linkGroup), (index, item) => {
            item.hide();
        });

        $.each(eval(getCheckedType() + 'Group'), (index, item) => {
            item.show();
        });
    };

    $articleForm.find('input[name="article_form[articleData][type]"]').change(initArticleForm);
    initArticleForm();
    console.log('ccccaaass');


    $articleForm.jsFormValidator({
        'groups': function () {
console.log('aaaaaaaaaaaaaaaa');
            const groups = [constant('\\Shopsys\\FrameworkBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT')];

            const checkedType = getCheckedType();
            if (checkedType === constant('\\App\\Model\\Article\\Article::TYPE_SITE')) {
                groups.push(constant('\\App\\Model\\Article\\Article::VALIDATION_GROUP_TYPE_SITE'));
            } else if (checkedType === constant('\\App\\Model\\Article\\Article::TYPE_LINK')) {
                groups.push(constant('\\App\\Model\\Article\\Article::VALIDATION_GROUP_TYPE_LINK'));
            }

            return groups;
        }
    });
}

(new Register()).registerCallback(validationArticle);
