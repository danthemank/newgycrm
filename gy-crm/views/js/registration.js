jQuery(document).ready(function($){

	
	const programCards = $('.program-class');
	const programInput = $('#input_programs');
	const slotsInput = $('#input_slots');
    
	function updateSelectedPrograms() {
		let selectedPrograms = $('.program-class.selected').map(function() {
	  		return $(this).data('class');
		})

		selectedPrograms = Array.from(selectedPrograms)

		let uniqueArr = selectedPrograms.filter((value, index, array) => {
			return array.indexOf(value) === index;
		}).join(',');

		programInput.val(uniqueArr);
	}
    
	function updateSelectedSlots() {
		const selectedSlots = $('.program-class.selected').map(function() {
			return $(this).data('slot');
		}).get().join(',');
		slotsInput.val(selectedSlots);
	}

	programCards.on('click', function() {
		$(this).toggleClass('selected');
		updateSelectedPrograms();
		updateSelectedSlots();
	});

    $("body").on("click", '#membership_form .collapse', function(e) {
        $(this).toggleClass("active");

		if ($(this).hasClass('athlete-panel')) {
			$(this).toggleClass("athlete-collapsed");
		}

		if (e.target.className !== 'add-athlete-btn') {
			$(this).next().slideToggle(200);
		} else {
			$(this).next().slideUp(200);
		}
	});

})
