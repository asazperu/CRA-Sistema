(function(){
  const box = document.getElementById('messages');
  if (box) box.scrollTop = box.scrollHeight;

  const openButtons = document.querySelectorAll('[data-open-modal]');
  openButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-open-modal');
      if (!id) return;
      const modal = document.getElementById(id);
      if (!modal) return;
      modal.hidden = false;
    });
  });

  const closeButtons = document.querySelectorAll('[data-close-modal]');
  closeButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-close-modal');
      if (!id) return;
      const modal = document.getElementById(id);
      if (!modal) return;
      modal.hidden = true;
    });
  });

  document.querySelectorAll('.modal-backdrop').forEach((modal) => {
    modal.addEventListener('click', (evt) => {
      if (evt.target === modal) {
        modal.hidden = true;
      }
    });
  });
})();
