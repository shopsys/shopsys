'use client';

import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';

export const CustomerInfo: FC = () => {
    const customer = useCurrentCustomerData();

    return (
        <div>
            {customer ? (
                <div className="flex flex-col gap-3">
                    <span>
                        {customer.firstName} {customer.lastName}
                    </span>
                    <span>{customer.email}</span>
                </div>
            ) : (
                'No customer data'
            )}
        </div>
    );
};
