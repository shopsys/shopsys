import { SpinnerIcon } from 'components/Basic/Icon/IconsSvg';

export const LoaderWithOverlay: FC = ({ className, dataTestId }) => (
    <div
        className="absolute inset-0 z-overlay flex h-full w-full items-center justify-center bg-greyLighter opacity-50"
        data-testid={dataTestId || 'loader-overlay'}
    >
        <SpinnerIcon className={className} />
    </div>
);
