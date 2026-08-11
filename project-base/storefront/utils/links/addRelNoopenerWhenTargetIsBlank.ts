export const addRelNoopenerWhenTargetIsBlank = (
    rel: string | undefined,
    target: string | undefined,
): string | undefined => {
    if (target?.toLowerCase() !== '_blank') {
        return rel;
    }

    const relValues = (rel || '').split(/\s+/).filter((value) => value !== '');

    if (!relValues.some((value) => value.toLowerCase() === 'noopener')) {
        relValues.push('noopener');
    }

    return relValues.join(' ');
};
