import Register from '../../common/utils/Register';

export default class RoleGroups {
    constructor(gridElement) {
        this.grid = gridElement;

        if (!this.grid) return;

        this.dependencies = this.getDependencies();
        this.selectors = {
            permissionCheckbox: '.js-roles-permission-checkbox',
            roleRow: '.js-roles-row',
            action: '.js-roles-action',
        };

        this.init();
        this.initStickyHeader();
    }

    init() {
        // Add change events to all permission checkboxes
        this.grid.querySelectorAll(this.selectors.permissionCheckbox).forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.handlePermissionChange(checkbox);
                this.updateToggleState(checkbox.dataset.role);
            });
        });

        // Add hover events to all role rows
        this.grid.querySelectorAll(this.selectors.roleRow).forEach(row => {
            const role = row.dataset.scope;
            if (!role) {
                console.warn('Role row missing data-scope attribute:', row);
                return;
            }
            row.addEventListener('mouseenter', () => {
                this.updateToggleState(role);
            });
        });

        // Add click events to all action buttons
        this.grid.querySelectorAll(this.selectors.action).forEach(button => {
            button.addEventListener('click', e => {
                e.preventDefault();
                const target = button.dataset.target;
                const wrapper = target ? this.grid.querySelector(`[data-scope="${target}"]`) : this.grid;
                this.toggleAll(wrapper, button.dataset.type === 'select');
            });
        });
    }

    initStickyHeader() {
        const header = this.grid.querySelector('.roles-grid__header');
        const content = this.grid.querySelector('.roles-grid__content');
        if (!header || !content) return;

        // Sync horizontal scroll between header and content
        content.addEventListener('scroll', () => {
            const scrollLeft = content.scrollLeft;
            header.style.transform = `translateX(${-scrollLeft}px)`;
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

    getCheckbox(role, permission) {
        return this.grid.querySelector(
            `${this.selectors.permissionCheckbox}[data-role="${role}"][data-permission="${permission}"]`,
        );
    }

    handlePermissionChange(checkbox) {
        const role = checkbox.dataset.role;
        const permission = checkbox.dataset.permission;
        const isChecked = checkbox.checked;

        const dependencyType = isChecked ? 'dependsOn' : 'dependents';
        const deps = this.dependencies[dependencyType]?.[permission] || [];
        deps.forEach(dep => {
            const dependencyCheckbox = this.getCheckbox(role, dep);
            if (dependencyCheckbox) {
                dependencyCheckbox.checked = isChecked;
            }
        });
    }

    toggleAll(wrapper, shouldSelect) {
        if (!wrapper) return;

        const checkboxes = wrapper.querySelectorAll(`${this.selectors.permissionCheckbox}:not(:disabled)`);
        const affectedRoles = new Set();

        checkboxes.forEach(checkbox => {
            if (checkbox.checked !== shouldSelect) {
                checkbox.checked = shouldSelect;
                if (checkbox.dataset.role) {
                    affectedRoles.add(checkbox.dataset.role);
                }
            }
        });

        // Update toggle states for affected roles
        affectedRoles.forEach(role => {
            this.updateToggleState(role);
        });
    }

    updateToggleState(role) {
        const roleRow = this.grid.querySelector(`${this.selectors.roleRow}[data-scope="${role}"]`);
        if (!roleRow) return;

        const roleCheckboxes = Array.from(roleRow.querySelectorAll(this.selectors.permissionCheckbox));
        if (roleCheckboxes.length === 0) return;

        const checkedCount = roleCheckboxes.filter(cb => cb.checked).length;
        const showDeselect = checkedCount > roleCheckboxes.length / 2;

        roleRow.querySelectorAll(this.selectors.action).forEach(button => {
            const isDeselectBtn = button.dataset.type === 'deselect';
            button.style.display = showDeselect === isDeselectBtn ? 'block' : 'none';
        });
    }

    static init() {
        document.querySelectorAll('.js-roles-grid').forEach(gridElement => {
            new RoleGroups(gridElement);
        });
    }
}

new Register().registerCallback(RoleGroups.init, 'RoleGroups.init');
