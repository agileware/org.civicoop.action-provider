cj(function() {
  cj('a.action-provider.start-download').each(function() {
    const href = cj(this).attr('href');
    if (href.length > 0) {
      window.location.href = href;
    }
  });
});
