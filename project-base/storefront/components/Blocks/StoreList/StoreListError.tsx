import { TIDs } from 'cypress/tids';

type StoreListErrorProps = {
    message: string;
};

export const StoreListError: FC<StoreListErrorProps> = ({ message }) => (
    <div
        className="mt-2.5 rounded-md border border-toast-border-error bg-toast-bg-error px-4 py-3 text-sm text-toast-text-error"
        data-tid={TIDs.store_list_error}
        role="alert"
    >
        {message}
    </div>
);
