import * as Icons from '../../../../public/svg';

export const IconsSvgMap = {
    Arrow: <Icons.Arrow />,
    ArrowRight: <Icons.ArrowRight />,
    Cart: <Icons.Cart />,
    NotImplementedYet: <Icons.NotImplementedYet />,
};

export type IconName = keyof typeof IconsSvgMap;
