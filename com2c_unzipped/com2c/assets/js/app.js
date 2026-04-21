document.addEventListener('click', function (e) {
  if (e.target.matches('[data-copy]')) {
    navigator.clipboard.writeText(e.target.getAttribute('data-copy'));
    e.target.innerText = 'Copied';
  }
});
