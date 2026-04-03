import { twJoin } from 'tailwind-merge';
import { getYIQContrastTextColor } from 'utils/colors/colors';
import { StyleguideSection } from './StyleguideElements';

const resolveColorValue = (colorValue: string, tailwindColors: Record<string, any>, depth = 0): string => {
    // prevent infinite recursion
    if (depth > 10) {
        return colorValue;
    }

    if (colorValue.startsWith('#')) {
        return colorValue;
    }

    if (colorValue.startsWith('var(--color-')) {
        // extract variable name from var(--color-brand-500) to brand-500
        const variableName = colorValue.replace('var(--color-', '').replace(')', '');
        const resolvedColor = tailwindColors[variableName];

        if (resolvedColor) {
            // resolve recursively if it's a CSS variable
            if (resolvedColor.startsWith('var(--color-')) {
                return resolveColorValue(resolvedColor, tailwindColors, depth + 1);
            }
            if (resolvedColor.startsWith('#')) {
                return resolvedColor;
            }
        }
    }

    return colorValue;
};

const getYIQContrastTextColorFromValue = (colorValue: string, tailwindColors: Record<string, any>) => {
    const resolvedColor = resolveColorValue(colorValue, tailwindColors);

    if (resolvedColor.startsWith('#')) {
        return getYIQContrastTextColor(resolvedColor);
    }

    return 'text-text-default';
};

type StyleguideColorsProps = { tailwindColors: Record<string, any> };
export const StyleguideColors: FC<StyleguideColorsProps> = ({ tailwindColors }) => {
    return (
        <StyleguideSection className="grid grid-cols-2 vl:grid-cols-4 gap-2 md:grid-cols-3" title="Colors">
            {Object.keys(tailwindColors).map((color, index) => (
                <div
                    key={index}
                    className={twJoin('flex items-center justify-center p-2 text-center')}
                    style={{ backgroundColor: resolveColorValue(tailwindColors[color] as string, tailwindColors) }}
                >
                    <span
                        className={twJoin(
                            'text-sm',
                            getYIQContrastTextColorFromValue(tailwindColors[color] as string, tailwindColors),
                        )}
                    >
                        {color}: {tailwindColors[color]}
                    </span>
                </div>
            ))}
        </StyleguideSection>
    );
};
