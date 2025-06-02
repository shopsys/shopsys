import { twJoin } from 'tailwind-merge';

type ArticleDate = {
    date: string;
    tid?: string;
    className?: string;
};

export const ArticleDate: FC<ArticleDate> = ({ date, tid, className }) => {
    return (
        <time
            className={twJoin('font-secondary text-text-less text-sm font-semibold', className)}
            data-tid={tid}
            dateTime={date}
        >
            {date}
        </time>
    );
};
