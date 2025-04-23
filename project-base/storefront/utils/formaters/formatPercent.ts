export const formatPercent = (value: string, minimumFractionDigits = 0): string | null | undefined => {
    try {
        const parsedValue = parseFloat(value);
        return `${parsedValue.toFixed(minimumFractionDigits)} %`;
    } catch (e) {
        return null;
    }
};
