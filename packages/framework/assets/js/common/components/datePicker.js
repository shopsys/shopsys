import $ from 'jquery';
import 'jquery-ui/datepicker';
import Register from '../register';
import constant from '../../admin/constant';

/* Czech initialisation for the jQuery UI date picker plugin. */
/* Written by Tomas Muller (tomas@tomas-muller.net). */
$.datepicker.regional['cs'] = {
    closeText: 'Zavřít',
    prevText: '&#x3c;Dříve',
    nextText: 'Později&#x3e;',
    currentText: 'Nyní',
    monthNames: ['leden', 'únor', 'březen', 'duben', 'květen', 'červen',
        'červenec', 'srpen', 'září', 'říjen', 'listopad', 'prosinec'],
    monthNamesShort: ['led', 'úno', 'bře', 'dub', 'kvě', 'čer',
        'čvc', 'srp', 'zář', 'říj', 'lis', 'pro'],
    dayNames: ['neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota'],
    dayNamesShort: ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'],
    dayNamesMin: ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'],
    weekHeader: 'Týd',
    dateFormat: 'dd.mm.yy',
    firstDay: 1,
    isRTL: false,
    showMonthAfterYear: false,
    yearSuffix: '' };
$.datepicker.setDefaults($.datepicker.regional['cs']);

export default function datePicker ($container) {
    $container.filterAllNodes('.js-date-picker').each(function () {
        // Loads regional settings for current locale
        const options = $.datepicker.regional[global.locale] || $.datepicker.regional[''];

        // Date format is fixed so that it is understood by back-end
        options.dateFormat = constant('\\Shopsys\\FrameworkBundle\\Form\\DatePickerType::FORMAT_JS');

        $(this).datepicker(options);
    });
};

(new Register()).registerCallback(datePicker);
