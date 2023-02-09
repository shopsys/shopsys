import { ImageSizesFragmentApi } from 'graphql/generated';

export type NotificationBarsType = {
    text: string | JSX.Element;
    rgbColor: string;
    images: ImageSizesFragmentApi[];
};
