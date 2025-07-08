import Register from '../../common/utils/Register';

const PERMISSION_VIEW = 'VIEW';
const PERMISSION_FULL = 'FULL';

export default class RoleGroups {
    constructor(gridElement) {
        this.grid = gridElement;

        if (!this.grid) return;

        this.dependencies = this.getDependencies();
        this.selectors = {
            selectAll: '.js-roles-select-all',
            permissionCheckbox: '.js-roles-permission-checkbox',
        };

        this.init();
    }

    init() {
        this.updateSelectAllStates();

        this.grid.addEventListener('change', e => {
            if (e.target.classList.contains('js-roles-select-all')) {
                this.handleSelectAll(e.target);
            } else if (e.target.classList.contains('js-roles-permission-checkbox')) {
                this.handlePermissionChange(e.target);
            }
            this.updateSelectAllStates();
        });
    }

    getDependencies() {
        const dependenciesData = this.grid.dataset.dependencies;
        if (dependenciesData) {
            try {
                return JSON.parse(dependenciesData);
            } catch (e) {
                console.warn('Failed to parse dependencies data:', e);
            }
        }
        return { dependsOn: {}, dependents: {} };
    }

    getRoleCheckboxes(role) {
        return this.grid.querySelectorAll(`[data-role="${role}"]${this.selectors.permissionCheckbox}`);
    }

    getPermissionCheckboxes(permission) {
        return this.grid.querySelectorAll(`[data-permission="${permission}"]${this.selectors.permissionCheckbox}`);
    }

    getCheckbox(role, permission) {
        return this.grid.querySelector(`[data-role="${role}"][data-permission="${permission}"]`);
    }

    handleSelectAll(selectAllCheckbox) {
        const permission = selectAllCheckbox.dataset.permission;
        const isChecked = selectAllCheckbox.checked;

        // check/uncheck all checkboxes in the column
        this.getPermissionCheckboxes(permission).forEach(checkbox => {
            if (checkbox.checked !== isChecked) {
                checkbox.checked = isChecked;
                // Trigger change event to handle dependencies
                checkbox.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    }

    handlePermissionChange(checkbox) {
        const role = checkbox.dataset.role;
        const permission = checkbox.dataset.permission;
        const isChecked = checkbox.checked;

        if (isChecked) {
            // Check dependencies
            this.setDependencyCheckboxes(role, permission, 'dependsOn', true);
            // Auto-check FULL if all other permissions are checked (full mode only)
            this.autoCheckFullIfNeeded(role);
        } else {
            // Uncheck dependents
            this.setDependencyCheckboxes(role, permission, 'dependents', false);
        }
    }

    setDependencyCheckboxes(role, permission, dependencyType, checked) {
        const deps = this.dependencies[dependencyType]?.[permission] || [];
        deps.forEach(dep => {
            const checkbox = this.getCheckbox(role, dep);
            if (checkbox) {
                checkbox.checked = checked;
            }
        });
    }

    autoCheckFullIfNeeded(role) {
        const roleCheckboxes = Array.from(this.getRoleCheckboxes(role));

        // Only auto-check in full mode (more than just VIEW and FULL)
        if (roleCheckboxes.length <= 2) return;

        const fullCheckbox = this.getCheckbox(role, PERMISSION_FULL);
        if (!fullCheckbox) return;

        const nonFullCheckboxes = roleCheckboxes.filter(cb => cb.dataset.permission !== PERMISSION_FULL);
        if (nonFullCheckboxes.every(cb => cb.checked)) {
            fullCheckbox.checked = true;
        }
    }

    updateSelectAllStates() {
        [PERMISSION_VIEW, PERMISSION_FULL].forEach(permission => {
            const selectAll = this.grid.querySelector(`[data-permission="${permission}"]${this.selectors.selectAll}`);
            if (!selectAll) return;

            const checkboxes = Array.from(this.getPermissionCheckboxes(permission));
            selectAll.checked = checkboxes.length > 0 && checkboxes.every(cb => cb.checked);
        });
    }

    static init() {
        document.querySelectorAll('.js-roles-grid').forEach(gridElement => {
            new RoleGroups(gridElement);
        });
    }
}

new Register().registerCallback(RoleGroups.init, 'RoleGroups.init');
