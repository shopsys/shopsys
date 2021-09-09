import * as Icons from '../../../../public/svg';

export const IconsSvgMap = {
    Arrow: <Icons.Arrow />,
    ArrowRight: <Icons.ArrowRight />,
    Cart: <Icons.Cart />,
    Cross: <Icons.Cross />,
    Search: <Icons.Search />,
    Chat: <Icons.Chat />,
    Marker: <Icons.Marker />,
    User: <Icons.User />,
    NotImplementedYet: <Icons.NotImplementedYet />,
};

export type IconName = keyof typeof IconsSvgMap;
