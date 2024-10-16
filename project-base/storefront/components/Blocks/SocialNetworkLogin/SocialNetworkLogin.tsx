import { SocialNetworkLoginLink } from './SocialNetworkLoginLink';
import { TypeLoginTypeEnum } from 'graphql/types';
import React from 'react';
import { usePersistStore } from 'store/usePersistStore';

type SocialNetworkLoginProps = {
    socialNetworks: TypeLoginTypeEnum[];
    shouldOverwriteCustomerUserCart: boolean | undefined;
};

export const SocialNetworkLogin: FC<SocialNetworkLoginProps> = ({
    socialNetworks,
    shouldOverwriteCustomerUserCart,
}) => {
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const productListUuids: string[] = Object.values(usePersistStore((store) => store.productListUuids));

    return (
        <div className="flex gap-4">
            {socialNetworks.map((socialNetwork) => {
                const url = {
                    pathname: `/social-network/login/${socialNetwork}`,
                    query: {
                        ...(cartUuid && { cartUuid }),
                        ...(shouldOverwriteCustomerUserCart !== undefined && { shouldOverwriteCustomerUserCart }),
                        ...(productListUuids.length > 0 && { productListUuids: productListUuids.join(',') }),
                    },
                };

                return <SocialNetworkLoginLink key={socialNetwork} href={url} socialNetwork={socialNetwork} />;
            })}
        </div>
    );
};
