import { TypeComplaintDetailFragment } from 'graphql/requests/complaints/fragments/ComplaintDetailFragment.generated';
import { ComplaintDetailBasicInfo } from './ComplaintDetailBasicInfo';
import { ComplaintDetailCustomerInfo } from './ComplaintDetailCustomerInfo';

type ComplaintDetailContentProps = {
    complaint: TypeComplaintDetailFragment;
};

export const ComplaintDetailContent: FC<ComplaintDetailContentProps> = ({ complaint }) => {
    return (
        <>
            <ComplaintDetailBasicInfo complaint={complaint} />

            <ComplaintDetailCustomerInfo complaint={complaint} />
        </>
    );
};
