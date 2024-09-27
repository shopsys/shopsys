export const UpsListItem: FC = ({ children }) => {
    return (
        <div className="flex flex-col items-center justify-start text-center text-balance gap-2.5 max-w-[245px] w-full">
            {children}
        </div>
    );
};
