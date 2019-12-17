import Register from '../common/register';
import Ajax from '../common/ajax';
import Window from './window';

export default class DomainIcon {

    static openDialog ($editDomainIcon) {
        Ajax.ajax({
            url: $editDomainIcon.closest('.js-domain-icon-edit-container').data('url'),
            success: (data) => {
                // eslint-disable-next-line no-new
                new Window({
                    content: data,
                    wide: true
                });
            }
        });
    };

    static uploadIcon ($form) {
        const $iconErrorListContainer = $('#js-domain-icon-errors');
        const $spinner = $('.js-overlay-spinner');
        $iconErrorListContainer.hide();
        $spinner.show();
        Ajax.ajax({
            url: $form.attr('action'),
            data: $form.serialize(),
            type: $form.attr('method'),
            dataType: 'json',
            success: (data) => {
                if (data['result'] === 'valid') {
                    document.location.reload();
                } else if (data['result'] === 'invalid') {
                    const $iconErrorList = $iconErrorListContainer.show().find('ul');
                    $iconErrorList.find('li').remove();
                    for (let i in data['errors']) {
                        $iconErrorList.append('<li>' + data['errors'][i] + '</li>');
                    }
                    $spinner.hide();
                }
            }
        });
    };

    static init ($container) {
        $container.filterAllNodes('.js-edit-domain-icon').click(function () {
            DomainIcon.openDialog($(this));
            return false;
        });
        $container.filterAllNodes('#domain_form_save').closest('form').submit(function () {
            DomainIcon.uploadIcon($(this));
            return false;
        });
    }

}

(new Register()).registerCallback(DomainIcon.init);
