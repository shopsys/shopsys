import { ComplaintDetailBasicInfo } from './ComplaintDetailBasicInfo';
import { ComplaintDetailCustomerInfo } from './ComplaintDetailCustomerInfo';
import { TypeComplaintDetailFragment } from 'graphql/requests/complaints/fragments/ComplaintDetailFragment.generated';

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
