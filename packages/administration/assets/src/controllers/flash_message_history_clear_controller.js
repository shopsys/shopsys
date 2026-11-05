import { Controller } from '@hotwired/stimulus';
import { clearFlashMessageHistory } from '../js/utils/flashMessageHistory';

export default class extends Controller {
    connect() {
        clearFlashMessageHistory();
    }
}
