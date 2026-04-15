import { FacebookSimpleIcon } from 'components/Basic/Icon/FacebookSimpleIcon';
import { GoogleIcon } from 'components/Basic/Icon/GoogleIcon';
import { SeznamIcon } from 'components/Basic/Icon/SeznamIcon';
import { TypeLoginTypeEnum } from 'graphql/types';

export const SocialNetworkIcon: FC<{ socialNetwork: TypeLoginTypeEnum }> = ({ socialNetwork }) => {
    switch (socialNetwork) {
        case TypeLoginTypeEnum.Facebook:
            return <FacebookSimpleIcon className="size-6 text-text-inverted" />;
        case TypeLoginTypeEnum.Seznam:
            return <SeznamIcon className="size-6 text-text-inverted" />;
        case TypeLoginTypeEnum.Google:
            return <GoogleIcon className="size-6" />;
        default:
            return null;
    }
};
