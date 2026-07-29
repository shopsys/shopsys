import {
    checkRoleGroupOptionsAreEnabled,
    checkUsersTableIsVisible,
    clickAddNewUserButton,
    clickDeleteUserButtonByEmail,
    clickEditUserButtonByEmail,
    confirmDeleteUser,
    fillAddUserForm,
    fillEditUserForm,
    submitManageUserForm,
    visitCustomerUsersPage,
} from './customerUsersSupport';
import { skipIfB2bNotConfigured } from 'e2e/b2bUser/b2bSupport';
import { b2bUrl } from 'fixtures/demodata';
import {
    check403PageIsVisible,
    checkAndHideSuccessToast,
    getSnapshotIndexingFunction,
    initializePersistStoreInLocalStorageToDefaultValues,
    loginAsB2bAccountant,
    loginAsB2bCustomerManager,
    loginAsB2bOwner,
    SNAPSHOT_GROUP,
    takeSnapshotAndCompare,
} from 'support';
import { TIDs } from 'tids';

const SUBGROUP_INDEX = 0;
const getSnapshotFullIndexAsString = getSnapshotIndexingFunction(SNAPSHOT_GROUP.CUSTOMER_USERS, SUBGROUP_INDEX);

describe('Customer Users (B2B) Tests', () => {
    skipIfB2bNotConfigured();

    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
    });

    describe('As B2B Owner', () => {
        beforeEach(() => {
            loginAsB2bOwner();
        });

        it('should display the customer users table', () => {
            visitCustomerUsersPage();
            checkUsersTableIsVisible();
            takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'customer-users-table', {
                blackout: [
                    { tid: TIDs.footer_social_links },
                    { tid: TIDs.footer_payment_images },
                    { tid: TIDs.footer_copyright },
                ],
            });
        });

        it('should open add user popup and add a new user', () => {
            const testEmail = 'cypress-test-user@shopsys.com';
            let createdUserUuid: string | undefined;

            cy.removeCustomerUserByEmailIfExistsViaApi(testEmail);

            cy.intercept('POST', '**/graphql/', (req) => {
                if (req.body?.operationName === 'AddNewCustomerUserMutation') {
                    req.continue((res) => {
                        createdUserUuid = res.body?.data?.AddNewCustomerUser?.uuid;
                    });
                }
            });

            visitCustomerUsersPage();
            clickAddNewUserButton();

            takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'add-user-popup', {
                capture: 'viewport',
                preserveFixed: [TIDs.layout_popup],
                blackout: [
                    { tid: TIDs.footer_social_links },
                    { tid: TIDs.footer_payment_images },
                    { tid: TIDs.footer_copyright },
                ],
            });

            fillAddUserForm({
                email: testEmail,
                firstName: 'Cypress',
                lastName: 'TestUser',
                telephone: '777123456',
            });
            submitManageUserForm();
            checkAndHideSuccessToast();
            cy.getByTID([TIDs.customer_users_table]).contains(testEmail).should('be.visible');

            cy.then(() => {
                if (createdUserUuid) {
                    cy.removeCustomerUserViaApi(createdUserUuid);
                }
            });
        });

        it('should open edit user popup and edit a user', () => {
            const testEmail = 'cypress-edit-test-user@shopsys.com';
            let userUuid: string;

            cy.removeCustomerUserByEmailIfExistsViaApi(testEmail);

            cy.getCustomerUserRoleGroupUuidForTest().then((roleGroupUuid) => {
                cy.addCustomerUserViaApi({
                    email: testEmail,
                    firstName: 'CypressEdit',
                    lastName: 'TestUser',
                    telephone: { countryCode: 'CZ', prefix: '+420', number: '777000111' },
                    roleGroupUuid,
                }).then((user) => {
                    userUuid = user.uuid;
                });
            });

            visitCustomerUsersPage();
            clickEditUserButtonByEmail(testEmail);

            takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'edit-user-popup', {
                capture: 'viewport',
                preserveFixed: [TIDs.layout_popup],
                blackout: [
                    { tid: TIDs.footer_social_links },
                    { tid: TIDs.footer_payment_images },
                    { tid: TIDs.footer_copyright },
                ],
            });

            fillEditUserForm({
                firstName: 'EditedFirst',
                lastName: 'EditedLast',
                telephone: '777654321',
            });
            submitManageUserForm();
            checkAndHideSuccessToast();

            cy.then(() => {
                cy.removeCustomerUserViaApi(userUuid);
            });
        });

        it('should open delete user popup and delete a user', () => {
            const testEmail = 'cypress-delete-test-user@shopsys.com';

            cy.removeCustomerUserByEmailIfExistsViaApi(testEmail);

            cy.getCustomerUserRoleGroupUuidForTest().then((roleGroupUuid) => {
                cy.addCustomerUserViaApi({
                    email: testEmail,
                    firstName: 'CypressDelete',
                    lastName: 'TestUser',
                    telephone: { countryCode: 'CZ', prefix: '+420', number: '777000222' },
                    roleGroupUuid,
                });
            });

            visitCustomerUsersPage();
            clickDeleteUserButtonByEmail(testEmail);

            takeSnapshotAndCompare(getSnapshotFullIndexAsString(), 'delete-user-popup', {
                capture: 'viewport',
                preserveFixed: [TIDs.layout_popup],
                blackout: [
                    { tid: TIDs.footer_social_links },
                    { tid: TIDs.footer_payment_images },
                    { tid: TIDs.footer_copyright },
                ],
            });

            confirmDeleteUser();
            checkAndHideSuccessToast();
        });
    });

    describe('As B2B Customer Manager', () => {
        beforeEach(() => {
            loginAsB2bCustomerManager();
        });

        it('should access the customer users page, enable role groups, and add a new user', () => {
            const testEmail = 'cypress-customer-manager-test-user@shopsys.com';
            let createdUserUuid: string | undefined;

            cy.removeCustomerUserByEmailIfExistsViaApi(testEmail);

            cy.intercept('GET', `**${b2bUrl.customer.users}`).as('customerUsersPage');
            cy.intercept('POST', '**/graphql/', (req) => {
                if (req.body?.operationName === 'AddNewCustomerUserMutation') {
                    req.continue((res) => {
                        createdUserUuid = res.body?.data?.AddNewCustomerUser?.uuid;
                    });
                }
            });

            visitCustomerUsersPage();
            cy.wait('@customerUsersPage').its('response.statusCode').should('eq', 200);
            checkUsersTableIsVisible();
            clickAddNewUserButton();
            checkRoleGroupOptionsAreEnabled();

            fillAddUserForm({
                email: testEmail,
                firstName: 'Cypress',
                lastName: 'CustomerManager',
                telephone: '777123457',
            });
            submitManageUserForm();
            checkAndHideSuccessToast();
            cy.getByTID([TIDs.customer_users_table]).contains(testEmail).should('be.visible');

            cy.then(() => {
                if (createdUserUuid) {
                    cy.removeCustomerUserViaApi(createdUserUuid);
                }
            });
        });
    });

    describe('Access control', () => {
        it('should show 403 for Accountant trying to access customer users page', () => {
            loginAsB2bAccountant();
            cy.visitB2bAndWaitForStableAndInteractiveDOM(b2bUrl.customer.users, { failOnStatusCode: false });
            check403PageIsVisible();
        });
    });
});
