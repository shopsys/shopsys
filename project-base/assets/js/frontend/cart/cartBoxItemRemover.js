import Register from 'framework/common/utils/register';
import Ajax from 'framework/common/utils/ajax';
import Window from '../utils/window';

export default class CartBoxItemRemover {

    static init () {
        document.getElementsByClassName('js-cart-box-item-remove-button').forEach(element => {
            element.addEventListener('click', event => {
                event.preventDefault();

                Ajax.ajax({
                    loaderElement: element,
                    url: element.getAttribute('href'),
                    type: 'post',
                    success: function (data) {
                        if (data.success === true) {
                            $('#js-cart-box').trigger('reload');
                        } else {
                            // eslint-disable-next-line no-new
                            new Window({
                                content: data.errorMessage
                            });
                        }
                    }
                });
            });
        });
    }
}

(new Register()).registerCallback(CartBoxItemRemover.init);
