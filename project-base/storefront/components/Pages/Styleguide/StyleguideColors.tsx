import { StyleguideSection } from './StyleguideElements';
import { twJoin } from 'tailwind-merge';

type StyleguideColorsProps = { tailwindColors: Record<string, any> };
export const StyleguideColors: FC<StyleguideColorsProps> = ({ tailwindColors }) => {
    return (
        <StyleguideSection
            className="grid grid-cols-[repeat(auto-fit,minmax(100px,250px))] items-stretch gap-1"
            title="Colors"
        >
            {Object.keys(tailwindColors).map((color, index) => (
                <div
                    key={index}
                    className={twJoin('flex h-24 items-center justify-center')}
                    style={{ backgroundColor: tailwindColors[color] as string }}
                >
                    <span className="text-textInverted mix-blend-difference">{color}</span>
                </div>
            ))}
        </StyleguideSection>
    );
};
