wp.domReady(() => {
  if (wp.data.select('core/editor').getCurrentPostType() === 'kongres_prezentacja' ||
    wp.data.select('core/editor').getCurrentPostType() === 'prelegenci') {

    // Ukryj pole autora
    wp.data.dispatch('core/edit-post').removeEditorPanel('post-author');

    // Ukryj pole daty
    wp.data.dispatch('core/edit-post').removeEditorPanel('post-publish');
  }
});
