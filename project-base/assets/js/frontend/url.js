export default function getBaseUrl () {
    return document.location.protocol
    + '//'
    + document.location.host
    + document.location.pathname;
};
