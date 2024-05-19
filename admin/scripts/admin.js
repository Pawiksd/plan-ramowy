jQuery(document).ready(function($) {
  // Enable sortable for the scene order list
  $('#scene_order').sortable({
    handle: '.handle',
    update: function(event, ui) {
      // Update hidden input field with the new order
      var sceneOrder = $(this).sortable('toArray', { attribute: 'data-id' });
      $('#scene_order_input').val(sceneOrder.join(','));
    }
  }).disableSelection();

  // Initialize Select2 for the prelegent select dropdown
  $('#prelegent_select').select2({
    width: '100%',
    minimumInputLength: 2
  });

  // Functionality for prelegenci list
  $('#prelegenci_list').sortable({
    handle: '.handle',
    update: function(event, ui) {
      updatePrelegenciInput();
    }
  }).disableSelection();

  $('#add_prelegent').on('click', function(e) {
    e.preventDefault();
    var prelegentId = $('#prelegent_select').val();
    if (prelegentId) {
      var prelegentName = $('#prelegent_select option:selected').text();
      var prelegentThumbnail = $('#prelegent_select option:selected').data('thumbnail');
      var listItem = '<li data-id="' + prelegentId + '"><span class="handle">☰</span><img src="' + prelegentThumbnail + '" class="prelegent-thumbnail" width="50" height="50"> ' + prelegentName + ' <a href="#" class="remove-prelegent">Usuń</a></li>';
      $('#prelegenci_list').append(listItem);
      updatePrelegenciInput();
    }
  });

  $('#prelegenci_list').on('click', '.remove-prelegent', function(e) {
    e.preventDefault();
    $(this).closest('li').remove();
    updatePrelegenciInput();
  });

  function updatePrelegenciInput() {
    var prelegentIds = $('#prelegenci_list li').map(function() {
      return $(this).attr('data-id');
    }).get();
    $('#prelegenci_input').val(prelegentIds.join(','));
  }
});
