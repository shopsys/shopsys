import { create } from 'zustand';

type LogoutStore = {
    logoutDeadlineByDomain: Record<number, number | undefined>;
    setLogoutDeadline: (domainId: number, deadline: number | undefined) => void;
};

export const useLogoutStore = create<LogoutStore>()((set) => ({
    logoutDeadlineByDomain: {},
    setLogoutDeadline: (domainId, deadline) => {
        set((state) => ({
            logoutDeadlineByDomain: { ...state.logoutDeadlineByDomain, [domainId]: deadline },
        }));
    },
}));
