import { SpinnerIcon } from 'components/Basic/Icon/IconsSvg';
import { DataTestIds } from 'cypress/dataTestIds';

export const LoaderWithOverlay: FC = ({ className }) => (
    <div
        className="absolute inset-0 z-overlay flex h-full w-full items-center justify-center bg-greyLighter opacity-50"
        data-testid={DataTestIds.loader_overlay}
    >
        <SpinnerIcon className={className} />
    </div>
);
