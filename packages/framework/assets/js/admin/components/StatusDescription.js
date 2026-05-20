import Register from '../../common/utils/Register';

const PRIORITY_AFTER_INIT_COMPONENTS = 600;
const STATUS_PUBLISHED = 'published';

function formatDateTime(date) {
    const pad = n => String(n).padStart(2, '0');

    return `${pad(date.getDate())}.${pad(date.getMonth() + 1)}.${date.getFullYear()} ${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
}

function initStatusDescription($container) {
    $container.filterAllNodes('select[data-js-status-description]').each(function () {
        const $select = $(this);
        const descriptions = $select.data('js-status-description');
        const $help = $(`#${$select.attr('id')}_help`);
        const publishDateInputId = $select.attr('id').replace('_statuses_', '_publishDates_');
        const $publishDateInput = $(`#${publishDateInputId}`);

        if (!$help.length) {
            return;
        }

        const update = value => {
            $help.html(descriptions[value] || '');

            if (value === STATUS_PUBLISHED && $publishDateInput.length && !$publishDateInput.val()) {
                $publishDateInput.val(formatDateTime(new Date()));
                $publishDateInput.trigger('change');
            }
        };

        if (this.tomselect) {
            this.tomselect.on('change', update);
            update(this.tomselect.getValue());
        } else {
            $select.on('change', () => update($select.val()));
            update($select.val());
        }
    });
}

new Register().registerCallback(initStatusDescription, 'initStatusDescription', PRIORITY_AFTER_INIT_COMPONENTS);
