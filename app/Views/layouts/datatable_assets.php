<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.DataTable === 'undefined') {
            return;
        }

        window.jQuery('table.js-datatable').each(function () {
            if (window.jQuery.fn.DataTable.isDataTable(this)) {
                return;
            }

            window.jQuery(this).DataTable({
                paging: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50],
                ordering: true,
                searching: true,
                info: true,
                responsive: true,
                order: [],
            });
        });
    });
</script>
