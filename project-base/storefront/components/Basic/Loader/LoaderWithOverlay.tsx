import { Loader } from './Loader';

export const LoaderWithOverlay: FC = ({ className }) => (
    <div className="absolute inset-0 z-overlay flex h-full w-full items-center justify-center bg-greyLighter opacity-50">
        <Loader className={className} />
    </div>
);
