/* global fxTheme */
export function initContactForm() {
  const form = document.getElementById('fx-contact-form');
  const toast = document.getElementById('fx-contact-toast');
  if (!form) {
    return;
  }

  const showToast = (message) => {
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add('fx-visible');
    toast.removeAttribute('hidden');
    setTimeout(() => {
      toast.classList.remove('fx-visible');
      toast.setAttribute('hidden', '');
    }, 3000);
  };

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    formData.append('action', 'fx_contact_submit');

    try {
      const response = await fetch(fxTheme.ajax_url, {
        method: 'POST',
        body: formData,
      });
      const data = await response.json();
      if (data.success) {
        form.reset();
        showToast(data.data.message);
      } else {
        showToast(data.data || 'Submission failed');
      }
    } catch (err) {
      showToast('An error occurred');
    }
  });
}