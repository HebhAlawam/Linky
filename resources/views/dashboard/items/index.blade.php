@extends('dashboard.layouts.main')

@section('title', 'إدارة العناصر')
@section('navbar-title', 'العناصر')
@section('page-title', 'إدارة العناصر')
@section('page-subtitle', 'إدارة المنتجات / الخدمات')


@push('styles')
<style>
    /* Success save flash */
    @keyframes rowSaveFlash {
    0%   { background: rgba(47, 179, 68, 0.14); }
    100% { background: transparent; }
    }

    tr.row-save-flash {
    animation: rowSaveFlash 10s ease;
    }

    .modal-header > .btn-close {
    left: 0 !important;
    right: auto !important;
    }

    /* Close button on the LEFT for specific alerts */
    .alert.alert-close-left {
    position: relative;
    padding-left: 2.75rem; /* مساحة للزر */
    }

    /* Force close to left side */
    .alert.alert-close-left > .btn-close{
    position: absolute;
    left: .75rem !important;
    right: auto !important;
    top: 50%;
    transform: translateY(-50%);
    }


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

  tr.is-editing {
    background: rgba(32, 107, 196, 0.04);
  }

  /* Keep validation feedback tidy inside table cells */
  #links-tbody .invalid-feedback{
    display: block;
    margin-top: .25rem;
    font-size: .75rem;
    line-height: 1.2;
    white-space: normal;
  }

  tr.is-row-invalid{
    background: rgba(214, 57, 57, 0.06);
  }

.form-check-input.js-link-toggle:checked {
  background-color: #2fb344;
  border-color: #2fb344;
}

/* ========= JS alerts host (bottom-left like quick-links) ========= */
#links-alert-host{
  position: fixed;
  left: 16px;
  bottom: 16px;
  z-index: 1080;
  width: min(520px, calc(100vw - 32px));
}

</style>
@endpush


@section('page-actions')
<a href="{{ route('dashboard.items.create') }}" class="btn btn-primary">
    <i class="ti ti-plus ms-2"></i>
    إضافة عنصر
</a>
@endsection

@section('content')

@include('partials.alerts')

@if ($items->isEmpty())
<div class="card">
    <div class="card-body text-center py-5">
        <span class="avatar avatar-xl bg-primary-lt text-primary mb-3">
            <i class="ti ti-tools-kitchen-2 fs-1"></i>
        </span>
        <h3 class="card-title mb-2">لا توجد عناصر بعد</h3>
        <p class="text-secondary mb-4">أضف أول منتج أو خدمة ليظهر في موقعك.</p>
        <a href="{{ route('dashboard.items.create') }}" class="btn btn-primary">
            <i class="ti ti-plus ms-2"></i>
            إضافة عنصر
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
                    <th>العنوان</th>
                    <th>السعر</th>
                    <th>القسم</th>
                    <th>النقرات</th>
                    <th>الظهور</th>
                    <th>إجراءات</th>
                </tr>
                </thead>

                <tbody id="items-tbody">
                @foreach($items as $item)
                <tr data-id="{{ $item->id }}">

                    {{-- ترتيب --}}
                    {{-- <td>{{ $item->display_order }}</td> --}}
<td class="text-center">
    <span class="js-drag-handle cursor-pointer">
        <i class="ti ti-grip-vertical"></i>
        <span class="small js-order-text">
            {{ $item->display_order }}
        </span>
    </span>
</td>
                    {{-- صورة --}}
                    <td>
                        <img src="{{ $item->image_url }}"
                                 alt="{{ $item->title_text }}"
                                 class="rounded"
                                 style="width:40px;height:40px;object-fit:cover"
                                 onerror="this.onerror=null;this.src='{{ asset('images/defaults/item.png') }}';">
                    </td>

                    {{-- عنوان --}}
                    <td>
                        <div class="fw-bold">{{ $item->title_text }}</div>
                        <small class="text-muted">
                            {{ Str::limit($item->short_description_text, 50) }}
                        </small>
                    </td>

                    {{-- السعر --}}
                    <td>
                        {{ $item->price_text }}
                    </td>

                    {{-- القسم --}}
                    <td>
                        {{ $item->category_name_text }}
                    </td>

                    {{-- النقرات --}}
                    <td>
                        <span class="badge bg-secondary-lt">
                            {{ $item->clicks }}
                        </span>
                    </td>

                    {{-- الظهور --}}
                    <td>
                        <form method="POST" action="{{ route('dashboard.items.toggle', $item->id) }}">
                            @csrf
                            @method('PATCH')

                            <input type="checkbox"
                                   class="form-check-input m-0"
                                   onchange="this.form.submit()"
                                   aria-label="{{ $item->is_visible ? 'إخفاء العنصر' : 'إظهار العنصر' }}"
                                   title="{{ $item->is_visible ? 'ظاهر' : 'مخفي' }}"
                                   {{ $item->is_visible ? 'checked' : '' }}>
                            <span class="badge bg-{{ $item->is_visible ? 'green' : 'secondary' }}-lt ms-2">
                                {{ $item->is_visible ? 'ظاهر' : 'مخفي' }}
                            </span>
                        </form>
                    </td>

                    {{-- إجراءات --}}
                    <td>
                        <div class="d-flex gap-2">

                            {{-- Edit --}}
                            <a href="{{ route('dashboard.items.edit', $item->id) }}"
                               class="text-primary"
                               aria-label="تعديل"
                               title="تعديل"
                               data-bs-toggle="tooltip"
                               data-bs-title="تعديل">
                                <i class="ti ti-edit"></i>
                            </a>

                            {{-- Delete --}}
<button
    type="button"
    class="js-icon-action text-danger border-0 bg-transparent"
    data-bs-toggle="modal"
    data-bs-target="#confirmDeleteModal"
    data-title="حذف العنصر"
    data-message="هل أنت متأكد من حذف هذا العنصر نهائياً؟"
    data-name="{{ $item->title_text }}"
    data-meta="#{{ $item->id }}"
    data-action="{{ route('dashboard.items.destroy', $item) }}"
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
const tbody = document.getElementById('items-tbody');
let dragRow = null;

tbody?.querySelectorAll('tr[data-id]').forEach(row => {

    row.draggable = true;

    row.addEventListener('dragstart', () => {
        dragRow = row;
        row.classList.add('table-active');
    });

    row.addEventListener('dragover', (e) => {
        e.preventDefault();

        const rect = row.getBoundingClientRect();
        const offset = e.clientY - rect.top;

        if (offset > rect.height / 2) {
            tbody.insertBefore(dragRow, row.nextSibling);
        } else {
            tbody.insertBefore(dragRow, row);
        }
    });

    row.addEventListener('dragend', () => {

        dragRow.classList.remove('table-active');

        const items = [];

        tbody.querySelectorAll('tr[data-id]').forEach((r, index) => {
            items.push({
                id: r.dataset.id,
                order: index
            });

            r.querySelector('.js-order-text').textContent = index;
        });

        fetch("{{ route('dashboard.items.reorder') }}", {
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
