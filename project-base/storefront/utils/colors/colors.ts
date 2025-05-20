const getRGBFromHex = (hex: string) => {
    const color = hex.replace(/#/g, '');
    return {
        r: parseInt(color.slice(0, 2), 16),
        g: parseInt(color.slice(2, 4), 16),
        b: parseInt(color.slice(4, 6), 16),
    };
};

export const getRGBColorString = (hex: string, opacity: number) => {
    const { r, g, b } = getRGBFromHex(hex);
    return `rgb(${r} ${g} ${b} / ${opacity})`;
};

export const getYIQContrastTextColor = (hex: string) => {
    const { r, g, b } = getRGBFromHex(hex);
    const yiq = (r * 299 + g * 587 + b * 114) / 1000;

    return yiq >= 128 ? 'text-text-default' : 'text-text-inverted';
};
