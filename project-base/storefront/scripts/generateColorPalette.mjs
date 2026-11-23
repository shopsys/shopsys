/**
 * Generates the palette color scales in styles/theme.css.
 *
 * Each family is built in OKLCH from its key colors. The default color of a family is always step 500,
 * lower steps are lighter and higher steps darker. Steps between key colors are interpolated, so the
 * lightness changes evenly and the hue stays steady. A family may pin more key colors (primary pins its
 * dark header color at 900).
 *
 * The theme is set by three key colors, like a design tool: primary (actions, links, the brand), secondary (the tonal
 * secondary button and highlights; by default the primary color, so the default theme has one hue) and neutral
 * (surfaces, borders and muted text, optionally tinted). Red, orange and green stay for the states.
 *
 * Usage: pnpm run generate-color-palette
 * The script rewrites the --color-<family>-<step> lines of each family in place and keeps everything else.
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';

const THEME_PATH = fileURLToPath(new URL('../styles/theme.css', import.meta.url));

const STEPS = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];

/**
 * Where each step sits between the light end (0) and step 500 (1), and between step 500 (0) and the dark end (1).
 * The neutral ladder has finer steps near white for surfaces and borders; the chromatic one follows the default
 * Tailwind v4 color scales.
 */
const LADDERS = {
    neutral: {
        lightEnd: 0.985,
        darkEnd: 0.145,
        light: { 50: 0, 100: 0.035, 200: 0.147, 300: 0.268, 400: 0.646, 500: 1 },
        dark: { 500: 0, 600: 0.285, 700: 0.45, 800: 0.7, 900: 0.856, 950: 1 },
        // the light neutral steps carry surfaces, borders and chips, so they stay close to gray instead of tinting the page
        lightChroma: { 50: 0.04, 100: 0.08, 200: 0.15, 300: 0.22, 400: 0.55, 500: 1 },
    },
    chromatic: {
        lightEnd: 0.975,
        darkEnd: 0.27,
        light: { 50: 0, 100: 0.11, 200: 0.25, 300: 0.46, 400: 0.76, 500: 1 },
        dark: { 500: 0, 600: 0.23, 700: 0.4, 800: 0.58, 900: 0.72, 950: 1 },
        lightChroma: { 50: 0.15, 100: 0.3, 200: 0.5, 300: 0.7, 400: 0.88, 500: 1 },
    },
};

// chroma of the darkest step relative to the last key color, when the family does not pin it
const DARK_END_CHROMA_RATIO = 0.5;

const FAMILIES = [
    { name: 'neutral', ladder: 'neutral', keyColors: { 500: '#6b7280' } },
    { name: 'primary', ladder: 'chromatic', keyColors: { 500: '#006cd6', 900: '#0f00a0' } },
    { name: 'secondary', ladder: 'chromatic', keyColors: { 500: '#006cd6', 900: '#0f00a0' } },
    { name: 'red', ladder: 'chromatic', keyColors: { 500: '#c23838' } },
    { name: 'orange', ladder: 'chromatic', keyColors: { 500: '#fcbd46' } },
    { name: 'green', ladder: 'chromatic', keyColors: { 500: '#15803d' } },
];

const toLinear = (channel) => (channel <= 0.04045 ? channel / 12.92 : ((channel + 0.055) / 1.055) ** 2.4);
const fromLinear = (channel) => (channel <= 0.0031308 ? 12.92 * channel : 1.055 * channel ** (1 / 2.4) - 0.055);

const hexToOklch = (hex) => {
    const [r, g, b] = [1, 3, 5].map((index) => toLinear(Number.parseInt(hex.slice(index, index + 2), 16) / 255));
    const l = Math.cbrt(0.4122214708 * r + 0.5363325363 * g + 0.0514459929 * b);
    const m = Math.cbrt(0.2119034982 * r + 0.6806995451 * g + 0.1073969566 * b);
    const s = Math.cbrt(0.0883024619 * r + 0.2817188376 * g + 0.6299787005 * b);
    const lightness = 0.2104542553 * l + 0.793617785 * m - 0.0040720468 * s;
    const a = 1.9779984951 * l - 2.428592205 * m + 0.4505937099 * s;
    const bAxis = 0.0259040371 * l + 0.7827717662 * m - 0.808675766 * s;

    return { lightness, chroma: Math.hypot(a, bAxis), hue: ((Math.atan2(bAxis, a) * 180) / Math.PI + 360) % 360 };
};

const oklchToLinearRgb = ({ lightness, chroma, hue }) => {
    const a = chroma * Math.cos((hue * Math.PI) / 180);
    const b = chroma * Math.sin((hue * Math.PI) / 180);
    const l = (lightness + 0.3963377774 * a + 0.2158037573 * b) ** 3;
    const m = (lightness - 0.1055613458 * a - 0.0638541728 * b) ** 3;
    const s = (lightness - 0.0894841775 * a - 1.291485548 * b) ** 3;

    return [
        4.0767416621 * l - 3.3077115913 * m + 0.2309699292 * s,
        -1.2684380046 * l + 2.6097574011 * m - 0.3413193965 * s,
        -0.0041960863 * l - 0.7034186147 * m + 1.707614701 * s,
    ];
};

const isInGamut = (color) => oklchToLinearRgb(color).every((channel) => channel >= -1e-4 && channel <= 1 + 1e-4);

// lowers the chroma until the color fits into sRGB, so the lightness and hue stay as planned
const clampToGamut = (color) => {
    if (isInGamut(color)) {
        return color;
    }

    let low = 0;
    let high = color.chroma;

    for (let iteration = 0; iteration < 30; iteration++) {
        const middle = (low + high) / 2;

        if (isInGamut({ ...color, chroma: middle })) {
            low = middle;
        } else {
            high = middle;
        }
    }

    return { ...color, chroma: low };
};

const oklchToHex = (color) =>
    `#${oklchToLinearRgb(clampToGamut(color))
        .map((channel) => Math.round(Math.min(1, Math.max(0, fromLinear(Math.min(1, Math.max(0, channel))))) * 255))
        .map((channel) => channel.toString(16).padStart(2, '0'))
        .join('')}`;

const mixHue = (fromHue, toHue, ratio) => {
    const difference = ((toHue - fromHue + 540) % 360) - 180;

    return (fromHue + difference * ratio + 360) % 360;
};

const mixColors = (from, to, ratio) => ({
    lightness: from.lightness + (to.lightness - from.lightness) * ratio,
    chroma: from.chroma + (to.chroma - from.chroma) * ratio,
    hue: mixHue(from.hue, to.hue, ratio),
});

const generateFamily = ({ keyColors, ladder: ladderName }) => {
    const ladder = LADDERS[ladderName];
    const keys = Object.fromEntries(Object.entries(keyColors).map(([step, hex]) => [Number(step), hexToOklch(hex)]));
    const defaultColor = keys[500];
    const darkestKeyStep = Math.max(...Object.keys(keys).map(Number));
    const darkKeys = {
        950: {
            lightness: ladder.darkEnd,
            chroma: keys[darkestKeyStep].chroma * DARK_END_CHROMA_RATIO,
            hue: keys[darkestKeyStep].hue,
        },
        ...keys,
    };
    const darkKeySteps = Object.keys(darkKeys).map(Number);

    const getDarkColor = (step) => {
        const lowerStep = Math.max(...darkKeySteps.filter((keyStep) => keyStep <= step));
        const upperStep = Math.min(...darkKeySteps.filter((keyStep) => keyStep >= step));
        const ratio =
            lowerStep === upperStep
                ? 0
                : (ladder.dark[step] - ladder.dark[lowerStep]) / (ladder.dark[upperStep] - ladder.dark[lowerStep]);

        return mixColors(darkKeys[lowerStep], darkKeys[upperStep], ratio);
    };

    const getLightColor = (step) => ({
        lightness: ladder.lightEnd + (defaultColor.lightness - ladder.lightEnd) * ladder.light[step],
        chroma: defaultColor.chroma * ladder.lightChroma[step],
        hue: defaultColor.hue,
    });

    return STEPS.map((step) => {
        if (keyColors[step]) {
            return { step, hex: keyColors[step].toLowerCase() };
        }

        return { step, hex: oklchToHex(step < 500 ? getLightColor(step) : getDarkColor(step)) };
    });
};

const replaceFamilyLines = (lines, family) => {
    const familyLinePattern = new RegExp(`^\\s*--color-${family.name}-\\d+:`);
    const firstIndex = lines.findIndex((line) => familyLinePattern.test(line));

    if (firstIndex === -1) {
        throw new Error(`styles/theme.css has no --color-${family.name}-* scale to replace`);
    }

    const indentation = lines[firstIndex].match(/^\s*/)[0];
    const familyLines = generateFamily(family).map(({ step, hex }) => `${indentation}--color-${family.name}-${step}: ${hex};`);
    const remainingLines = lines.filter((line) => !familyLinePattern.test(line));

    remainingLines.splice(firstIndex, 0, ...familyLines);

    return remainingLines;
};

const themeLines = readFileSync(THEME_PATH, 'utf8').split('\n');
const generatedThemeLines = FAMILIES.reduce(replaceFamilyLines, themeLines);

writeFileSync(THEME_PATH, generatedThemeLines.join('\n'));

for (const family of FAMILIES) {
    console.log(`${family.name.padEnd(10)} ${generateFamily(family).map(({ step, hex }) => `${step}:${hex}`).join(' ')}`);
}
