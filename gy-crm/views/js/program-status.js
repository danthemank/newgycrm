jQuery(document).ready(function($){
    $('#admin_filters #slot-filter-dropdown').on('change', function() {
        let slot = $(this).val()
        let programClass = $('#admin_filters #class-filter-dropdown').val()
        let meta = $('#admin_filters #slot-filter-dropdown option[value="'+slot+'"]').data('meta')

        if (programClass !== '' && slot !== '' && meta !== '') {
            window.location.href = '/wp-admin/admin.php?page=program-status&class='+programClass+'&slot='+slot+'&meta='+meta
        }
    })

    if ($('#admin_filters #slot-filter-dropdown').val() !== '') {
        $('#admin_filters #slot-filter').show()
    }


    $('#program_status .search-submit').on('click', function(e) {
        e.preventDefault();

        let search = $('#program_status input[name="search"]').val();
        let programClass = $('#program_status #is_class').data('class')

        if (programClass) {
            window.location.href = '/wp-admin/admin.php?page=program-status&class='+programClass+'&search='+search;
        }

    });
});