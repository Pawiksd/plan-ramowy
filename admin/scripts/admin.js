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
});
