import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { TIDs } from 'cypress/tids';

export const Loader: FC = ({ className }) => <SpinnerIcon className={className} tid={TIDs.loader} />;
