/* global fortiveaX */
export function initContactForm() {
  const form = document.getElementById('fortiveax-contact-form');
  const toast = document.getElementById('fortiveax-contact-toast');
  if (!form) {
    return;
  }

  const showToast = (message) => {
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add('visible');
    toast.removeAttribute('hidden');
    setTimeout(() => {
      toast.classList.remove('visible');
      toast.setAttribute('hidden', '');
    }, 3000);
  };

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    formData.append('action', 'fortiveax_contact_submit');

    try {
      const response = await fetch(fortiveaX.ajax_url, {
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