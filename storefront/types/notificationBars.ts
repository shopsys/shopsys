import { ImageType } from 'types/image';

export type NotificationBarsType = {
    text: string | JSX.Element;
    rgbColor: string;
    image: ImageType | null;
};
