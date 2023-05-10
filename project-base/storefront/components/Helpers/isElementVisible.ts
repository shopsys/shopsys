export const isElementVisible = (visibleRanges: { min: number; max: number }[], width: number): boolean => {
    for (const range of visibleRanges) {
        if (range.min <= width && range.max >= width) {
            return true;
        }
    }

    return false;
};
