function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const menuButton = document.querySelector('.mobile-menu-btn');

    if (!sidebar || window.innerWidth > 992) return;

    const willOpen = !sidebar.classList.contains('active');
    sidebar.classList.toggle('active', willOpen);
    overlay?.classList.toggle('active', willOpen);
    document.body.classList.toggle('sidebar-open', willOpen);
    menuButton?.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const menuButton = document.querySelector('.mobile-menu-btn');

    sidebar?.classList.remove('active');
    overlay?.classList.remove('active');
    document.body.classList.remove('sidebar-open');
    menuButton?.setAttribute('aria-expanded', 'false');
}

function closeSidebarOnLink() {
    if (window.innerWidth <= 992) {
        closeSidebar();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992) {
            closeSidebar();
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') closeSidebar();
    });

    // Label kolom membuat setiap baris tabel tetap mudah dibaca saat ditumpuk di layar ponsel.
    document.querySelectorAll('table').forEach(function(table) {
        const rows = Array.from(table.querySelectorAll('tr'));
        const headerRow = rows.find(function(row) {
            return row.querySelectorAll('th').length > 0 && row.querySelectorAll('td').length === 0;
        });
        if (!headerRow) return;
        headerRow.classList.add('mobile-table-header');

        const headers = Array.from(headerRow.querySelectorAll('th')).map(function(cell) {
            return cell.textContent.trim().replace(/\s+/g, ' ');
        });
        rows.forEach(function(row) {
            if (row === headerRow || row.closest('tfoot')) return;

            let columnIndex = 0;
            Array.from(row.cells).forEach(function(cell) {
                if (cell.tagName !== 'TD') return;
                const span = Math.max(1, Number(cell.getAttribute('colspan')) || 1);
                if (span === 1 && headers[columnIndex]) {
                    cell.setAttribute('data-label', headers[columnIndex]);
                }
                columnIndex += span;
            });
        });
        table.classList.add('mobile-table-cards');
    });
});
