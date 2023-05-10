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
    Remove: <Icons.Remove />,
    NotImplementedYet: <Icons.NotImplementedYet />,
    Triangle: <Icons.Triangle />,
    Sort: <Icons.Sort />,
    RemoveBold: <Icons.RemoveBold />,
    RemoveThin: <Icons.RemoveThin />,
    Plus: <Icons.Plus />,
    Filter: <Icons.Filter />,
    Phone: <Icons.Phone />,
    Instagram: <Icons.Instagram />,
    Youtube: <Icons.Youtube />,
    MapMarker: <Icons.MapMarker />,
    Warning: <Icons.Warning />,
    Checkmark: <Icons.Checkmark />,
    Spinner: <Icons.Spinner />,
    Info: <Icons.Info />,
    Compare: <Icons.Compare />,
    ArrowSecondary: <Icons.ArrowSecondary />,
};

export type IconName = keyof typeof IconsSvgMap;
