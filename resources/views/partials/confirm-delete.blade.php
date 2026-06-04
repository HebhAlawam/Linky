<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalTitle">تأكيد الحذف</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
      </div>

      <div class="modal-body">
        <div class="alert alert-warning mb-0">
          <div class="fw-semibold mb-1" id="deleteModalMessage">
            هل أنت متأكد أنك تريد الحذف؟
          </div>

          <div class="small text-muted">
            <span id="deleteModalName">—</span>
            {{-- <span class="mx-1" id="deleteModalDot">•</span>
            <span id="deleteModalMeta">—</span> --}}
          </div>
        </div>

        <div class="small text-danger mt-2" id="deleteModalHint">
          هذا الإجراء لا يمكن التراجع عنه.
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>

<form id="confirmDeleteForm" method="POST" class="m-0">
  @csrf
  <input type="hidden" name="_method" id="confirmDeleteMethod" value="DELETE">


  <div id="confirmDeleteExtra"></div>

  <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">
    <i class="bi bi-trash"></i> <span id="confirmDeleteBtnText">حذف</span>
  </button>
</form>

      </div>

    </div>
  </div>
</div>


@push('scripts')
<script>

document.addEventListener('DOMContentLoaded', () => {
  const modalEl = document.getElementById('confirmDeleteModal');
  if (!modalEl) return;

  const applyOptions = (opts) => {
    // القيم الافتراضية
    const defaults = {
      title: 'تأكيد الحذف',
      message: 'هل أنت متأكد؟',
      name: '—',
      meta: '—',
      action: '',
      method: 'DELETE',
      extra: '',
      btnText: 'حذف',
      btnClass: 'btn-danger'
    };

    const config = { ...defaults, ...opts };

    // تحديث النصوص في المودال
    document.getElementById('deleteModalTitle').textContent = config.title;
    document.getElementById('deleteModalMessage').textContent = config.message;
    document.getElementById('deleteModalName').textContent = config.name || '—';
    // document.getElementById('deleteModalMeta').textContent = config.meta || '—';

    // التحكم في ظهور النقطة الفاصلة
    const dotEl = document.getElementById('deleteModalDot');
    if (dotEl) {
        dotEl.style.display = (config.meta && config.meta !== '—') ? 'inline' : 'none';
    }

    // تحديث الفورم والأكشن
    const form = document.getElementById('confirmDeleteForm');
    if (form) form.action = config.action;

    // تحديث ميثود الحذف (Laravel _method)
    const methodInput = document.getElementById('confirmDeleteMethod');
    if (methodInput) methodInput.value = config.method.toUpperCase();

    // تحديث الزر
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const confirmBtnText = document.getElementById('confirmDeleteBtnText');
    if (confirmBtnText) confirmBtnText.textContent = config.btnText;
    if (confirmBtn) confirmBtn.className = 'btn ' + config.btnClass;

    // التعامل مع الحقول الإضافية (Extra Inputs)
    const extraBox = document.getElementById('confirmDeleteExtra');
    if (extraBox) {
      extraBox.innerHTML = '';
      if (config.extra && config.extra.trim()) {
        config.extra.split('&').forEach(pair => {
          const [k, v] = pair.split('=');
          if (!k) return;
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = decodeURIComponent(k);
          input.value = decodeURIComponent(v ?? '');
          extraBox.appendChild(input);
        });
      }
    }
  };

  // المصلّح الرئيسي: استخدام closest لضمان جلب البيانات من الزر
modalEl.addEventListener('show.bs.modal', (event) => {
  const btn = event.relatedTarget?.closest('button');
  if (!btn) return;

  applyOptions({
    title: btn.dataset.title,
    message: btn.dataset.message,
    name: btn.dataset.name,
    meta: btn.dataset.meta,
    action: btn.dataset.action,
    method: btn.dataset.method || 'DELETE',
    extra: btn.dataset.extra || '',
    btnText: btn.dataset.btnText,
    btnClass: btn.dataset.btnClass,
  });
});

  window.openConfirmDelete = (opts) => {
    applyOptions(opts);
    const m = bootstrap.Modal.getOrCreateInstance(modalEl);
    m.show();
  };
});
</script>
@endpush
