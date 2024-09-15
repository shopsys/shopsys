import { StyleguideSection } from './StyleguideElements';
import { twJoin } from 'tailwind-merge';

type StyleguideColorsProps = { tailwindColors: Record<string, any> };
export const StyleguideColors: FC<StyleguideColorsProps> = ({ tailwindColors }) => {
    return (
        <StyleguideSection
            className="grid items-stretch grid-cols-[repeat(auto-fit,minmax(100px,250px))] gap-1"
            title="Colors"
        >
            {Object.keys(tailwindColors).map((color, index) => (
                <div
                    key={index}
                    className={twJoin('h-24 flex justify-center items-center')}
                    style={{ backgroundColor: tailwindColors[color] as string }}
                >
                    <span className="text-textInverted mix-blend-difference">{color}</span>
                </div>
            ))}
        </StyleguideSection>
    );
};
