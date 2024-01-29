jQuery(document).ready(function($){
    let programStatus = $('#program_status tbody tr')
	$('#program_status #search_account').on('keyup', function() {

		let search = $(this).val()
		
		$.each(programStatus, function() {

			let rows = $(this).children()
			rows = rows.text().toLowerCase()

			if (rows.includes(search.toLowerCase()) && rows !== 'no items') {
				$(this).removeClass('hidden');
				$(this).addClass('row-show');
			} else {
				$(this).removeClass('row-show');
				$(this).addClass('hidden');
			}
		})
	})

    $('#program_status #status_filter').on('change', function() {
        let status = $(this).val()
        let urlParams = new URLSearchParams(window.location.search);
        let slot = urlParams.get('slot');
        let meta = urlParams.get('meta');
        let programClass = $('#admin_filters #class-filter-dropdown').val()

        if (programClass !== '' && slot !== '' && meta !== '') {
            window.location = '/wp-admin/admin.php?page=program-status&class='+programClass+'&slot='+slot+'&meta='+meta+'&status='+status
        }

    })

    $('body').on('click', '#admin_filters #slot-filter-dropdown > li', function() {
        let slot = $(this).data('slot')
        let meta = $(this).data('meta')
        let programClass = $('#admin_filters #class-filter-dropdown').val()
        let status = $('#program_status #status_filter').val()

        if (programClass !== '' && slot !== '' && meta !== '') {
            window.location.href = '/wp-admin/admin.php?page=program-status&class='+programClass+'&slot='+slot+'&meta='+meta+'&status='+status
        }
    })
});