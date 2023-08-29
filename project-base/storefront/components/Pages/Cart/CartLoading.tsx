import { Loader } from 'components/Basic/Loader/Loader';
import { Webline } from 'components/Layout/Webline/Webline';

const TEST_IDENTIFIER = 'blocks-cartloading';

export const CartLoading: FC = () => {
    return (
        <Webline className="h-96">
            <div className="my-32 text-center text-2xl" data-testid={TEST_IDENTIFIER}>
                <Loader className="h-10 w-10" />
            </div>
        </Webline>
    );
};
