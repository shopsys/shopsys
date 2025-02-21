import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { TIDs } from 'cypress/tids';

export const LoaderWithOverlay: FC = ({ className }) => (
    <div
        className="z-overlay bg-imageOverlay absolute inset-0 flex h-full w-full items-center justify-center rounded-xs backdrop-blur-xs"
        tid={TIDs.loader_overlay}
    >
        <SpinnerIcon className={className} />
    </div>
);
