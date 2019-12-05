import $ from 'jquery';

export default function windowClose () {
    $('#js-window').trigger('windowFastClose');
};
