import { Alert } from 'components/Basic/Alert/Alert';
import { TIDs } from 'cypress/tids';

type StoreListErrorProps = {
    message: string;
};

export const StoreListError: FC<StoreListErrorProps> = ({ message }) => (
    <Alert className="mt-2.5" role="alert" tid={TIDs.store_list_error} variant="error">
        {message}
    </Alert>
);
