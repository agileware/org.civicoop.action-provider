cj(function() {
  function actionProviderStartDownloads() {
    setTimeout(() => {
      cj('a.action-provider.start-download').each(function() {
        const href = cj(this).attr('href');
        if (href.length > 0) {
          window.location.href = href;
          cj(this).removeClass('start-download');
        }
      });
    }, 1500);
  }
  actionProviderStartDownloads();

    // Preprocess all CRM ajax calls to display messages
  cj(document).ajaxSuccess(function(event, xhr, settings) {
    if ((!settings.dataType || settings.dataType == 'json') && xhr.responseText) {
      actionProviderStartDownloads();
    }
  });
});
