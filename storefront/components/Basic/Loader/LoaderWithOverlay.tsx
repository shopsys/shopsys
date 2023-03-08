import { Loader } from './Loader';

type LoaderWithOverlayProps = {
    iconSize?: number;
};

export const LoaderWithOverlay: FC<LoaderWithOverlayProps> = ({ iconSize }) => (
    <div className="absolute inset-0 z-overlay flex h-full w-full items-center justify-center bg-greyLighter opacity-50">
        <Loader iconSize={iconSize} />
    </div>
);
