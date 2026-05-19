export const normalizeTelephone = (telephone: string | null | undefined): string | undefined =>
    telephone?.replace(/\s/g, '');
