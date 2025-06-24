import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { TIDs } from 'cypress/tids';
import { twMergeCustom } from 'utils/twMerge';

type LoaderWithOverlayProps = {
    isFullScreen?: boolean;
    overlayClassName?: string;
};

export const LoaderWithOverlay: FC<LoaderWithOverlayProps> = ({
    className,
    overlayClassName,
    isFullScreen = false,
}) => (
    <div
        tid={TIDs.loader_overlay}
        className={twMergeCustom(
            'z-overlay bg-overlay-image flex w-full items-center justify-center backdrop-blur-xs',
            isFullScreen ? 'fixed inset-0 h-screen' : 'absolute inset-0 h-max min-h-full',
            overlayClassName,
        )}
    >
        <SpinnerIcon className={className} />
    </div>
);
