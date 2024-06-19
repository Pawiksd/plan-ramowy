jQuery(document).ready(function($) {
  // Initialize Select2
    $('#scene_select').select2();

    // Add scene to the list
    $('#add_scene_button').click(function() {
        var selectedScenes = $('#scene_select').val();
        selectedScenes.forEach(function(sceneId) {
            var sceneTitle = $('#scene_select option[value="' + sceneId + '"]').text();
            $('#scene_order').append(
                '<li data-id="' + sceneId + '">' +
                '<span class="handle">☰</span>' +
                '<input type="text" name="scene_name_' + sceneId + '" placeholder="' + sceneTitle + '">' +
                '<input type="text" class="hex-color" name="scene_bg_color_' + sceneId + '" placeholder="Kolor tła" maxlength="7" pattern="#[a-fA-F0-9]{6}">' +
                '<input type="text" class="hex-color" name="scene_text_color_' + sceneId + '" placeholder="Kolor tekstu" maxlength="7" pattern="#[a-fA-F0-9]{6}">' +
                '<input type="text" name="scene_text_size_' + sceneId + '" placeholder="Wielkość tekstu">' +
                '<button type="button" class="remove_scene_button">Usuń</button>' +
                '</li>'
            );
            applyColorPicker();
        });
        $('#scene_select').val(null).trigger('change');
        updateSceneOrderInput();
    });

    // Enable sortable for the scene order list
    $('#scene_order').sortable({
        handle: '.handle',
        update: function(event, ui) {
            updateSceneOrderInput();
        }
    }).disableSelection();

    // Remove scene from the list
    $(document).on('click', '.remove_scene_button', function() {
        $(this).closest('li').remove();
        updateSceneOrderInput();
    });

  function updateSceneOrderInput() {
    var sceneOrder = $('#scene_order').sortable('toArray', { attribute: 'data-id' });
    var cleanedOrder = sceneOrder.filter(function(item) { return item.trim() !== ''; });
    $('#scene_order_input').val(cleanedOrder.join(','));
  }
    // Initialize color pickers
    function applyColorPicker() {
        $('.hex-color').wpColorPicker();
    }

    applyColorPicker();


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

  $('#moderator_select').select2();
  $('#add_moderator').on('click', function(e) {
    e.preventDefault();
    var selectedModerator = $('#moderator_select').val();
    if (selectedModerator) {
      var selectedText = $('#moderator_select option:selected').text();
      var selectedThumbnail = $('#moderator_select option:selected').data('thumbnail');
      var newListItem = '<li data-id="' + selectedModerator + '"><span class="handle">☰</span><img src="' + selectedThumbnail + '" class="moderator-thumbnail" /><span>' + selectedText + '</span><a href="#" class="remove-moderator">Usuń</a></li>';
      $('#moderators_list').append(newListItem);
      updateModeratorsInput();
    }
  });

  $('#moderators_list').on('click', '.remove-moderator', function(e) {
    e.preventDefault();
    $(this).closest('li').remove();
    updateModeratorsInput();
  });

  function updateModeratorsInput() {
    var moderators = [];
    $('#moderators_list li').each(function() {
      moderators.push($(this).data('id'));
    });
    $('#moderators_input').val(moderators.join(','));
  }

  $('#moderators_list').sortable({
    handle: '.handle',
    update: function() {
      updateModeratorsInput();
    }
  });
});
