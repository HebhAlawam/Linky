import { createApp, ref, h } from 'vue';
import IconPickerModal from '../components/LinkPage/IconPickerModal.vue';

const mountEl = document.getElementById('icon-picker-root');

if (mountEl) {
  const open = ref(false);
  const targetInputId = ref(null);

  function openPicker(inputId) {
    targetInputId.value = inputId;
    open.value = true;
  }

  function onSelect(iconClass) {
    const id = targetInputId.value;
    if (!id) return;

    const input = document.getElementById(id);
    if (input) {
      input.value = iconClass;
      input.dispatchEvent(new Event('input', { bubbles: true }));
      input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // Optional: auto preview sync by convention
    const previewId = id.replace('_class', '_preview');
    const preview = document.getElementById(previewId);
    if (preview) preview.className = iconClass || 'ti ti-star';
  }

  // Global function for Blade buttons
  window.openIconPicker = openPicker;

  const app = createApp({
    setup() {
      return () =>
        h(IconPickerModal, {
          modelValue: open.value,
          'onUpdate:modelValue': (v) => (open.value = v),
          onSelect: onSelect,
        });
    },
  });

  app.mount(mountEl);
}
