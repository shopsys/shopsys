type RgbColor = {
    r: number;
    g: number;
    b: number;
};

const getRGBFromColorString = (colorString: string): RgbColor | null => {
    const color = colorString.trim().replace(/^#/, '');

    if (/^[\da-f]{6}$/i.test(color)) {
        return {
            r: parseInt(color.slice(0, 2), 16),
            g: parseInt(color.slice(2, 4), 16),
            b: parseInt(color.slice(4, 6), 16),
        };
    }

    const rgbMatch = colorString.match(/rgba?\(([^)]+)\)/i);

    if (rgbMatch === null) {
        return null;
    }

    const [r, g, b] = rgbMatch[1]
        .split(/[,\s/]+/)
        .filter(Boolean)
        .slice(0, 3)
        .map(Number);

    if ([r, g, b].some((channel) => !Number.isFinite(channel))) {
        return null;
    }

    return {
        r,
        g,
        b,
    };
};

export const getRGBColorString = (hex: string, opacity: number) => {
    const { r, g, b } = getRGBFromColorString(hex) ?? { r: 0, g: 0, b: 0 };

    return `rgb(${r} ${g} ${b} / ${opacity})`;
};

const getRelativeLuminance = ({ r, g, b }: RgbColor): number => {
    const [red, green, blue] = [r, g, b].map((channel) => {
        const normalizedChannel = channel / 255;

        return normalizedChannel <= 0.03928 ? normalizedChannel / 12.92 : ((normalizedChannel + 0.055) / 1.055) ** 2.4;
    });

    return 0.2126 * red + 0.7152 * green + 0.0722 * blue;
};

const getContrastRatio = (color1: RgbColor, color2: RgbColor): number => {
    const color1Luminance = getRelativeLuminance(color1);
    const color2Luminance = getRelativeLuminance(color2);
    const lighter = Math.max(color1Luminance, color2Luminance);
    const darker = Math.min(color1Luminance, color2Luminance);

    return (lighter + 0.05) / (darker + 0.05);
};

export const getYIQContrastTextColor = (colorString: string) => {
    const color = getRGBFromColorString(colorString);

    if (color === null) {
        return 'text-text-inverted';
    }

    const defaultTextColor = { r: 37, g: 40, b: 61 };
    const invertedTextColor = { r: 255, g: 255, b: 255 };

    return getContrastRatio(color, defaultTextColor) >= getContrastRatio(color, invertedTextColor)
        ? 'text-base-black'
        : 'text-text-inverted';
};
