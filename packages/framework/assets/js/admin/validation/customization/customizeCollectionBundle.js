import $ from 'jquery';
import FormChangeInfo from '../../components/formChangeInfo';
import { highlightSubmitButtons } from '../../../common/validation/validation';

export function addNewItemToCollection (collectionSelector, itemIndex) {
    $($(collectionSelector)).jsFormValidator('addPrototype', itemIndex);
    FormChangeInfo.showInfo();
}

export function removeItemFromCollection (collectionSelector, itemIndex) {
    if (itemIndex === undefined) {
        throw Error('ItemIndex is undefined while remove item from collections');
    }
    const $collection = $(collectionSelector);
    $($collection).jsFormValidator('delPrototype', itemIndex);
    highlightSubmitButtons($collection.closest('form'));
    $collection.jsFormValidator('validate');
    FormChangeInfo.showInfo();
}
