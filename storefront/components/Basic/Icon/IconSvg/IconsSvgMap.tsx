import * as Icons from '../../../../public/svg';

export const IconsSvgMap = {
    Arrow: <Icons.Arrow />,
    ArrowRight: <Icons.ArrowRight />,
    Cart: <Icons.Cart />,
    Cross: <Icons.Cross />,
    Search: <Icons.Search />,
    NotImplementedYet: <Icons.NotImplementedYet />,
};

export type IconName = keyof typeof IconsSvgMap;
