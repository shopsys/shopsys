import { Webline } from 'components/Layout/Webline/Webline';
import { twJoin } from 'tailwind-merge';

export const FooterContainer: FC<{ className?: string }> = ({ children, className }) => {
    return (
        <div className={twJoin('border-border-less border-t', className)}>
            <Webline className="py-5 lg:py-8">{children}</Webline>
        </div>
    );
};
