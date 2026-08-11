// A page opened in a new tab can manipulate the opener window (reverse tabnabbing), which is prevented by
// rel="noopener". Any existing rel value is kept, only noopener is merged into it.
// Other target values are left alone — the attribute is meaningless for _self, _parent and _top, and for a
// named target it would make the link open a new tab instead of navigating that window or frame.
export const addRelNoopenerWhenTargetIsBlank = (rel, target) => {
    if ((target || '').toLowerCase() !== '_blank') {
        return rel;
    }

    const relValues = (rel || '').split(/\s+/).filter(value => value !== '');

    if (relValues.some(value => value.toLowerCase() === 'noopener')) {
        return rel;
    }

    relValues.push('noopener');

    return relValues.join(' ');
};
