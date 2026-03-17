/**
 * Success handling strategies:
 *
 * 1. State-driven UI — form shows a "thank you" page after submit
 *    Examples: Contact, ResetPassword, PersonalDataOverview, PersonalDataExport
 *
 * 2. Toast — form in popup or inline edit, shows a toast notification
 *    Examples: EditProfile, Newsletter, DeliveryAddress, Complaint, Inquiry, Watchdog
 *
 * 3. Redirect — success means redirect to another page
 *    Examples: Login, Registration
 *
 * 4. Toast + redirect — confirmation toast followed by redirect
 *    Examples: NewPassword
 */
import { ReactElement } from 'react';

// eslint-disable-next-line @typescript-eslint/no-empty-object-type
export type FormFieldMeta<TForm, TExtra = {}> = {
    [K in keyof TForm]: {
        name: K;
        label: string | ReactElement;
    } & TExtra;
};

// eslint-disable-next-line @typescript-eslint/no-empty-object-type
export type FormMeta<TForm, TMessages extends Record<string, string> = {}, TExtra = {}> = {
    formName: string;
    messages: TMessages;
    fields: FormFieldMeta<TForm, TExtra>;
};
