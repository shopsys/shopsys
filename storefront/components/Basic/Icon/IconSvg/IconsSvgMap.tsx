import * as Icons from 'public/svg';

export const IconsSvgMap = {
    Arrow: <Icons.Arrow />,
    ArrowRight: <Icons.ArrowRight />,
    Cart: <Icons.Cart />,
    Cross: <Icons.Cross />,
    Search: <Icons.Search />,
    Chat: <Icons.Chat />,
    Marker: <Icons.Marker />,
    User: <Icons.User />,
    Close: <Icons.Close />,
    Menu: <Icons.Menu />,
    Replace: <Icons.Replace />,
    Remove: <Icons.Remove />,
    NotImplementedYet: <Icons.NotImplementedYet />,
    Triangle: <Icons.Triangle />,
    Sort: <Icons.Sort />,
    RemoveBold: <Icons.RemoveBold />,
};

export type IconName = keyof typeof IconsSvgMap;
