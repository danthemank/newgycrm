jQuery(document).ready(function($){

  $('body').on('click', '.edit-btn', function() {
    let invoiceId = $(this).data('invoiceid')
    
    if (invoiceId !== '') {
      $('.invoice-id').text(invoiceId)
      $('#invoice_id').val(invoiceId)
    }

    let classId = $(this).data('class')
		let slotId = $(this).data('slot')

    if (classId && slotId) {
      if ($(this).data('type') !== 'no-auto') {
        $('#confirm_delete_class #delete_class_id').val(classId)
        $('#confirm_delete_class #delete_slot_id').val(slotId)
      }
    }

    let modalId = $(this).data('modal')

    $(modalId).draggable({
      handle: ".modal-header",
    })

    $(modalId).resizable()

    $(modalId).modal({
      fadeDuration: 250
    });

      return false;
    });


  let carrier = $('#carrier_option')

  if (carrier.text()) {
    $('#account-billing option').each(function() {
      if ($(this).val() == carrier.text()) {
        $(this).attr('selected', true)
      }
    })
  }

  $('.nav-tab li').click(function() {

    $('.nav-tab li.active')
    $('.nav-tab li.active').removeClass('active');
    $(this).addClass('active');

    let height = $('.nav-tab li.active .content-holder').outerHeight();
    let top = $( '.nav-tab li.active .content-holder' ).position().top;

    let position = height+top

    $('.child-details-editing .submit-container').css('top', position+'px')
      });
  $('.nav-tab li:first-child').addClass('active');
})