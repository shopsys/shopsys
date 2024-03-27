import { StyleguideSection } from './StyleguideElements';
import React from 'react';
import { twJoin } from 'tailwind-merge';

class ColorItem {
    constructor(
        public name: string,
        public className: string,
        // eslint-disable-next-line no-empty-function
    ) {}
}

const colorsTwClass = [
    new ColorItem('Primary', 'bg-primary'),
    new ColorItem('primaryLight', 'bg-primaryLight'),
    new ColorItem('primaryDarker', 'bg-primaryDarker'),
    new ColorItem('whitesmoke', 'bg-whitesmoke'),
    new ColorItem('black', 'bg-black'),
    new ColorItem('orange', 'bg-orange'),
    new ColorItem('orangeLight', 'bg-orangeLight'),
    new ColorItem('orangeDarker', 'bg-orangeDarker'),
    new ColorItem('border', 'bg-border'),
    new ColorItem('red', 'bg-red'),
    new ColorItem('redLight', 'bg-redLight'),
    new ColorItem('green', 'bg-green'),
    new ColorItem('greenLight', 'bg-greenLight'),
    new ColorItem('greenVeryLight', 'bg-greenVeryLight'),
    new ColorItem('greenDark', 'bg-greenDark'),
    new ColorItem('grey', 'bg-grey'),
    new ColorItem('greyLight', 'bg-greyLight'),
    new ColorItem('greyVeryLight', 'bg-greyVeryLight'),
    new ColorItem('greyDark', 'bg-greyDark'),
    new ColorItem('greyDarker', 'bg-greyDarker'),
    new ColorItem('blueLight', 'bg-blueLight'),
    new ColorItem('blue', 'bg-blue'),
    new ColorItem('creamWhite', 'bg-creamWhite'),
    new ColorItem('inStock', 'bg-inStock'),
];

export const StyleguideColors: FC = () => {
    return (
        <StyleguideSection
            className="grid items-stretch grid-cols-[repeat(auto-fit,minmax(100px,250px))] gap-1"
            title="Colors"
        >
            {colorsTwClass.map((color) => (
                <div key={color.name} className={twJoin('h-24 flex justify-center items-center', color.className)}>
                    <span className="text-white mix-blend-difference">{color.name}</span>
                </div>
            ))}
        </StyleguideSection>
    );
};
