import { StyleguideSection } from './StyleguideElements';
import React from 'react';
import { twJoin } from 'tailwind-merge';
import tailwindConfigRaw from 'tailwind.config.js';
import resolveConfig from 'tailwindcss/resolveConfig';

const fullConfig = resolveConfig(tailwindConfigRaw);

const tailwindColors = fullConfig.theme.backgroundColor;

export const StyleguideColors: FC = () => {
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
