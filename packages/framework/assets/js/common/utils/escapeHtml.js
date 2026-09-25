import $ from 'jquery';
export const escapeHtml = string => $('<textarea/>').text(string).html();
