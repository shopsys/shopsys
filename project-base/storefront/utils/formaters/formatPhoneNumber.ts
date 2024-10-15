export const formatPhoneNumber = (phoneNumber: string): string | null | undefined => {
    if (phoneNumber && phoneNumber.length === 9) {
        return phoneNumber.replace(/(\d{3})(\d{3})(\d{3})/, '$1 $2 $3');
    }
    return phoneNumber ? phoneNumber : undefined;
};
