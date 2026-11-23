import { useEffect, useMemo, useState } from 'react';
import { StyleguideCode, StyleguideSubSection } from './StyleguideElements';
import { ThemeColorBlock, ThemeColorFamily } from './themeColors';

const resolveColorValue = (colorValue: string, colorValues: Record<string, string>, depth = 0): string => {
    // prevent infinite recursion
    if (depth > 10 || !colorValue.startsWith('var(--color-')) {
        return colorValue;
    }

    // extract variable name from var(--color-brand-500) to brand-500
    const variableName = colorValue.replace('var(--color-', '').replace(')', '');
    const referencedColor = colorValues[variableName] as string | undefined;

    return referencedColor ? resolveColorValue(referencedColor, colorValues, depth + 1) : colorValue;
};

const getColorValues = (themeColorBlocks: ThemeColorBlock[]) =>
    Object.fromEntries(
        themeColorBlocks.flatMap((block) =>
            block.families.flatMap((family) => family.colors.map((color) => [color.name, color.value])),
        ),
    );

/**
 * Tailwind emits a theme variable only when a utility or another variable uses it,
 * so a token missing from the page styles is not used anywhere in the storefront.
 */
const getUnusedColorNames = (colorNames: string[]) => {
    const rootStyle = window.getComputedStyle(document.documentElement);

    return colorNames.filter((colorName) => !rootStyle.getPropertyValue(`--color-${colorName}`).trim());
};

// #rrggbbaa with an alpha below ff
const isTranslucentColor = (colorValue: string) => /^#[0-9a-f]{6}(?!ff)[0-9a-f]{2}$/i.test(colorValue);

// half light, half dark, so a translucent white or black stays visible
const TRANSLUCENT_SWATCH_BACKDROP = 'linear-gradient(90deg, #ffffff 50%, #25283d 50%)';

type StyleguideColorsProps = { themeColorBlocks: ThemeColorBlock[] };

export const StyleguideColors: FC<StyleguideColorsProps> = ({ themeColorBlocks }) => {
    const colorValues = useMemo(() => getColorValues(themeColorBlocks), [themeColorBlocks]);
    const colorNames = Object.keys(colorValues);
    const [unusedColorNames, setUnusedColorNames] = useState<string[]>([]);

    useEffect(() => {
        setUnusedColorNames(getUnusedColorNames(colorNames));
    }, [colorValues]);

    return (
        <>
            <p className="text-sm">
                {unusedColorNames.length} of {colorNames.length} tokens are marked{' '}
                <StyleguideCode>unused</StyleguideCode>: no utility or other token uses them, so Tailwind leaves them
                out of the CSS.
            </p>

            {themeColorBlocks.map((block) => (
                <StyleguideSubSection key={block.title} title={block.title}>
                    <div className="flex flex-col gap-6">
                        {block.families.map((family) => (
                            <StyleguideColorFamily
                                key={family.name}
                                colorValues={colorValues}
                                family={family}
                                unusedColorNames={unusedColorNames}
                            />
                        ))}
                    </div>
                </StyleguideSubSection>
            ))}
        </>
    );
};

type StyleguideColorFamilyProps = {
    family: ThemeColorFamily;
    colorValues: Record<string, string>;
    unusedColorNames: string[];
};

const StyleguideColorFamily: FC<StyleguideColorFamilyProps> = ({ colorValues, family, unusedColorNames }) => (
    <div className="flex flex-col gap-2">
        <p className="font-semibold text-text-less text-xs uppercase tracking-wider">{family.name}</p>

        <div className="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {family.colors.map((color) => (
                <StyleguideColorSwatch
                    key={color.name}
                    isUnused={unusedColorNames.includes(color.name)}
                    name={color.name}
                    resolvedValue={resolveColorValue(color.value, colorValues)}
                    value={color.value}
                />
            ))}
        </div>
    </div>
);

type StyleguideColorSwatchProps = {
    isUnused: boolean;
    name: string;
    value: string;
    resolvedValue: string;
};

const StyleguideColorSwatch: FC<StyleguideColorSwatchProps> = ({ isUnused, name, resolvedValue, value }) => {
    const isReference = value !== resolvedValue;
    const referencedName = value.replace('var(--color-', '').replace(')', '');
    const valueDescription = isReference ? `→ ${referencedName} · ${resolvedValue}` : value;
    const swatchBackdrop = isTranslucentColor(resolvedValue) ? TRANSLUCENT_SWATCH_BACKDROP : undefined;

    return (
        <div className="flex items-center gap-3 rounded-lg border border-border-less p-2">
            <span
                className="size-10 shrink-0 overflow-hidden rounded-md border border-border-less"
                style={{ backgroundImage: swatchBackdrop }}
            >
                <span className="block size-full" style={{ backgroundColor: resolvedValue }} />
            </span>

            <span className="flex min-w-0 flex-col text-xs">
                <span className="flex min-w-0 items-center gap-1.5">
                    <code className="truncate font-semibold" title={name}>
                        {name}
                    </code>
                    {isUnused && (
                        <span className="shrink-0 rounded-sm bg-background-warning px-1 font-semibold text-[10px] uppercase">
                            unused
                        </span>
                    )}
                </span>
                <span className="truncate text-text-less" title={value}>
                    {valueDescription}
                </span>
            </span>
        </div>
    );
};
