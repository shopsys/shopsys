type StoreListEmptyProps = {
    message: string;
    description: string;
};

export const StoreListEmpty: FC<StoreListEmptyProps> = ({ message, description }) => (
    <div className="mt-2.5 flex flex-col items-center justify-center rounded-xl bg-background-more p-5 text-center">
        <p className="font-secondary font-semibold text-lg text-text-default">{message}</p>
        <p className="text-sm text-text-less">{description}</p>
    </div>
);
