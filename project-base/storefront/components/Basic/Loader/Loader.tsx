import { Icon } from '../Icon/Icon';
import { Spinner } from '../Icon/IconsSvg';

export const Loader: FC = ({ className }) => <Icon icon={<Spinner />} className={className} />;
