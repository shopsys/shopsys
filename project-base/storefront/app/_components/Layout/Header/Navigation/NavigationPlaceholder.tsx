import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

export const NavigationPlaceholder: FC = async () => {
    return (
        <nav className="hidden lg:block">
            <div className="relative flex w-full gap-5 py-4">
                <Skeleton className="h-8 w-28 rounded-sm max-lg:hidden" />
                <Skeleton className="h-8 w-20 rounded-sm max-lg:hidden" />
                <Skeleton className="h-8 w-24 rounded-sm max-lg:hidden" />
                <Skeleton className="h-8 w-32 rounded-sm max-lg:hidden" />
                <Skeleton className="h-8 w-20 rounded-sm max-lg:hidden" />
            </div>
        </nav>
    );
};
