import '../bootstrap/file_input';
import Register from '../../common/register';

export function initBootstrapFileInput ($container) {
    $container.filterAllNodes('input[type=file]').bootstrapFileInput();
    $container.filterAllNodes('.file-inputs').bootstrapFileInput();
}

(new Register()).registerCallback(initBootstrapFileInput);
