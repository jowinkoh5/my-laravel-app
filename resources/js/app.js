/**
 * Mirrors a "select all" checkbox onto every row checkbox inside the same
 * bulk-delete form, and keeps it in sync when rows are toggled individually.
 */
document.querySelectorAll('[data-bulk-delete]').forEach((form) => {
    const selectAll = form.querySelector('[data-select-all]');
    const rowCheckboxes = Array.from(form.querySelectorAll('[data-row-checkbox]'));

    if (!selectAll || rowCheckboxes.length === 0) {
        return;
    }

    const syncSelectAll = () => {
        const checkedCount = rowCheckboxes.filter((checkbox) => checkbox.checked).length;

        selectAll.checked = checkedCount === rowCheckboxes.length;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < rowCheckboxes.length;
    };

    selectAll.addEventListener('change', () => {
        rowCheckboxes.forEach((checkbox) => {
            checkbox.checked = selectAll.checked;
        });

        selectAll.indeterminate = false;
    });

    rowCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', syncSelectAll));

    syncSelectAll();
});
