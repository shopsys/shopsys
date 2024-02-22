import { Loader } from 'components/Basic/Loader/Loader';
import { Webline } from 'components/Layout/Webline/Webline';

export const CartLoading: FC = () => {
    return (
        <Webline className="h-96">
            <div className="my-32 text-center text-2xl">
                <Loader className="h-10 w-10" />
            </div>
        </Webline>
    );
};
