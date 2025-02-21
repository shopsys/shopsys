export const UpsListItem: FC = ({ children }) => {
    return (
        <div className="flex w-full max-w-[245px] flex-col items-center justify-start gap-2.5 text-center text-balance">
            {children}
        </div>
    );
};
