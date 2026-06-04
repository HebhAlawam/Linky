@extends('dashboard.layouts.main')

@section('title', 'إدارة التصنيفات')
@section('navbar-title', 'التصنيفات')
@section('page-title', 'إدارة التصنيفات')
@section('page-subtitle', 'تنظيم العناصر ضمن أقسام واضحة')

@push('styles')
<style>
  .js-icon-action{
    background: transparent !important;
    border: 0 !important;
    padding: 0 !important;
    margin: 0 !important;
    line-height: 1;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
  }
  .js-icon-action i{ font-size: 18px; }
  .js-icon-action:hover{ opacity: .75; }
  .form-check-input.js-category-toggle:checked {
    background-color: #2fb344;
    border-color: #2fb344;
  }
</style>
@endpush

@section('page-actions')
<a href="{{ route('dashboard.categories.create') }}" class="btn btn-primary">
    <i class="ti ti-plus ms-2"></i>
    إضافة تصنيف
</a>
@endsection

@section('content')
@include('partials.alerts')

@if ($categories->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <span class="avatar avatar-xl bg-primary-lt text-primary mb-3">
                <i class="ti ti-category fs-1"></i>
            </span>
            <h3 class="card-title mb-2">لا توجد تصنيفات بعد</h3>
            <p class="text-secondary mb-4">ابدأ بإضافة أول تصنيف لتنظيم منتجاتك.</p>
            <a href="{{ route('dashboard.categories.create') }}" class="btn btn-primary">
                <i class="ti ti-plus ms-2"></i>
                إضافة تصنيف
            </a>
        </div>
    </div>
@else
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-vcenter">
                <thead>
                <tr>
                    <th>ترتيب</th>
                    <th>الصورة</th>
                    <th>الاسم</th>
                    <th>الوصف</th>
                    <th>عدد العناصر</th>
                    <th>الظهور</th>
                    <th>إجراءات</th>
                </tr>
                </thead>

                <tbody id="categories-tbody">
                @foreach($categories as $category)
                <tr data-id="{{ $category->id }}">
                    <td class="text-center">
                        <span class="js-drag-handle cursor-pointer">
                            <i class="ti ti-grip-vertical"></i>
                            <span class="small js-order-text">{{ $category->display_order }}</span>
                        </span>
                    </td>

                    <td>
                        <img src="{{ $category->image_url }}"
                             alt="{{ $category->name_text }}"
                             class="rounded"
                             style="width:40px;height:40px;object-fit:cover"
                             onerror="this.onerror=null;this.src='{{ asset('images/defaults/category.png') }}';">
                    </td>

                    <td>
                        <div class="fw-bold">{{ $category->name_text }}</div>
                    </td>

                    <td class="text-muted">
                        {{ Str::limit($category->description_text, 70) ?: '—' }}
                    </td>

                    <td>
                        <span class="badge bg-secondary-lt">{{ $category->items_count }}</span>
                    </td>

                    <td>
                        <form method="POST" action="{{ route('dashboard.categories.toggle', $category) }}">
                            @csrf
                            @method('PATCH')
                            <label class="d-inline-flex align-items-center gap-2 mb-0">
                                <input type="checkbox"
                                       class="form-check-input js-category-toggle m-0"
                                       onchange="this.form.submit()"
                                       aria-label="{{ $category->is_visible ? 'إخفاء التصنيف' : 'إظهار التصنيف' }}"
                                       title="{{ $category->is_visible ? 'ظاهر' : 'مخفي' }}"
                                       {{ $category->is_visible ? 'checked' : '' }}>
                                <span class="badge bg-{{ $category->is_visible ? 'green' : 'secondary' }}-lt">
                                    {{ $category->is_visible ? 'ظاهر' : 'مخفي' }}
                                </span>
                            </label>
                        </form>
                    </td>

                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('dashboard.categories.edit', $category) }}" class="text-primary" aria-label="تعديل" title="تعديل" data-bs-toggle="tooltip" data-bs-title="تعديل">
                                <i class="ti ti-edit"></i>
                            </a>

                            <button
                                type="button"
                                class="js-icon-action text-danger border-0 bg-transparent"
                                data-bs-toggle="modal"
                                data-bs-target="#confirmDeleteModal"
                                data-title="حذف التصنيف"
                                data-message="هل أنت متأكد من حذف هذا التصنيف نهائياً؟"
                                data-name="{{ $category->name_text }}"
                                data-meta="#{{ $category->id }}"
                                data-action="{{ route('dashboard.categories.destroy', $category) }}"
                                data-method="DELETE"
                                data-btn-text="نعم، احذف"
                                data-btn-class="btn-danger"
                                aria-label="حذف"
                                title="حذف">
                                <i class="ti ti-x"
                                   data-bs-toggle="tooltip"
                                   data-bs-title="حذف"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
const tbody = document.getElementById('categories-tbody');
let dragRow = null;

tbody?.querySelectorAll('tr[data-id]').forEach(row => {
    row.draggable = true;

    row.addEventListener('dragstart', () => {
        dragRow = row;
        row.classList.add('table-active');
    });

    row.addEventListener('dragover', (event) => {
        event.preventDefault();

        const rect = row.getBoundingClientRect();
        const offset = event.clientY - rect.top;

        if (offset > rect.height / 2) {
            tbody.insertBefore(dragRow, row.nextSibling);
        } else {
            tbody.insertBefore(dragRow, row);
        }
    });

    row.addEventListener('dragend', () => {
        dragRow.classList.remove('table-active');

        const items = [];

        tbody.querySelectorAll('tr[data-id]').forEach((categoryRow, index) => {
            items.push({
                id: categoryRow.dataset.id,
                order: index
            });

            categoryRow.querySelector('.js-order-text').textContent = index;
        });

        fetch("{{ route('dashboard.categories.reorder') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ items })
        });
    });
});
</script>
@endpush
