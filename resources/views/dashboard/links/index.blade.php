@extends('dashboard.layouts.main')

@section('title', 'إدارة روابط التواصل')
@section('navbar-title', 'روابط التواصل')
@section('page-title', 'إدارة روابط التواصل')
@section('page-subtitle', 'إدارة روابط وحسابات الصفحة العامة')

@push('styles')
<style>
  .linky-link-slot {
    border: 1px solid var(--tblr-border-color);
    border-radius: 10px;
    padding: 14px;
    height: 100%;
    background: var(--tblr-bg-surface);
  }
  .linky-link-slot.is-empty {
    border-style: dashed;
    background: var(--tblr-bg-surface-secondary);
    opacity: .86;
  }
  .linky-link-icon {
    width: 38px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: 20px;
  }
  .linky-link-url {
    display: block;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--tblr-secondary);
  }
  .linky-link-actions {
    flex-wrap: wrap;
  }
  .linky-link-value {
    min-height: 1.25rem;
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
</style>
@endpush

@section('content')
@include('partials.alerts')

@php
    $displayUrl = function ($link) {
        $url = trim((string) $link->url);

        if ($link->type === 'phone') {
            return preg_replace('/^tel:/i', '', $url);
        }

        if ($link->type === 'whatsapp') {
            $phone = preg_replace('/\D+/', '', $url);

            return $phone ?: 'واتساب';
        }

        return $link->title_text ?: Str::limit($url, 38);
    };

    $displayTitle = function ($link, string $fallback) {
        return $link->type === 'social'
            ? $fallback
            : ($link->title_text ?: $fallback);
    };
@endphp

<div class="card mb-4">
    <div class="card-header">
        <div>
            <h3 class="card-title">وسائل التواصل الأساسية</h3>
            <div class="card-subtitle">كل وسيلة أساسية يمكن إضافتها مرة واحدة فقط.</div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach ($fixedSlots as $slot)
                @php($link = $slot['link'])
                <div class="col-md-6 col-xl-4">
                    <div class="linky-link-slot {{ $link ? '' : 'is-empty' }}">
                        <div class="d-flex gap-3 align-items-start">
                            <span class="linky-link-icon bg-primary-lt text-primary">
                                <i class="{{ $link?->icon ?: $slot['icon'] }}"></i>
                            </span>
                            <div class="flex-fill min-w-0">
                                <div class="fw-bold">{{ $link ? $displayTitle($link, $slot['label']) : $slot['label'] }}</div>
                                @if ($link)
                                    <a href="{{ $link->url }}" target="_blank" rel="noopener" class="linky-link-url linky-link-value small text-decoration-underline" dir="ltr" title="{{ $link->url }}">
                                        {{ $displayUrl($link) }}
                                    </a>
                                    <div class="mt-2 d-flex align-items-center gap-2 linky-link-actions">
                                        <span class="badge bg-secondary-lt">النقرات: {{ $link->clicks }}</span>
                                        <a href="{{ route('dashboard.links.edit', $link) }}" class="btn btn-sm btn-outline-primary">تعديل</a>
                                        <button
                                            type="button"
                                            class="js-icon-action text-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#confirmDeleteModal"
                                            data-title="حذف الرابط"
                                            data-message="هل أنت متأكد من حذف هذا الرابط نهائياً؟"
                                            data-name="{{ $link->title_text }}"
                                            data-meta="#{{ $link->id }}"
                                            data-action="{{ route('dashboard.links.destroy', $link) }}"
                                            data-method="DELETE"
                                            data-btn-text="نعم، احذف"
                                            data-btn-class="btn-danger">
                                            <i class="ti ti-trash" data-bs-toggle="tooltip" data-bs-title="حذف"></i>
                                        </button>
                                    </div>
                                @else
                                    <div class="text-secondary small mb-2">غير مضاف بعد</div>
                                    <a href="{{ route('dashboard.links.create', ['type' => $slot['type']]) }}" class="btn btn-sm btn-outline-primary">إضافة</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <div>
            <h3 class="card-title">حسابات السوشال</h3>
            <div class="card-subtitle">كل منصة اجتماعية يمكن إضافتها مرة واحدة فقط.</div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach ($socialSlots as $slot)
                @php($link = $slot['link'])
                <div class="col-md-6 col-xl-3">
                    <div class="linky-link-slot {{ $link ? '' : 'is-empty' }}">
                        <div class="d-flex gap-3 align-items-start">
                            <span class="linky-link-icon bg-primary-lt text-primary">
                                <i class="{{ $link?->icon ?: $slot['icon'] }}"></i>
                            </span>
                            <div class="flex-fill min-w-0">
                                <div class="fw-bold">{{ $link ? $displayTitle($link, $slot['label']) : $slot['label'] }}</div>
                                @if ($link)
                                    <a href="{{ $link->url }}" target="_blank" rel="noopener" class="linky-link-url linky-link-value small text-decoration-underline" dir="ltr" title="{{ $link->url }}">
                                        {{ $displayUrl($link) }}
                                    </a>
                                    <div class="mt-2 d-flex align-items-center gap-2 linky-link-actions">
                                        <span class="badge bg-secondary-lt">النقرات: {{ $link->clicks }}</span>
                                        <a href="{{ route('dashboard.links.edit', $link) }}" class="btn btn-sm btn-outline-primary">تعديل</a>
                                        <button
                                            type="button"
                                            class="js-icon-action text-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#confirmDeleteModal"
                                            data-title="حذف الرابط"
                                            data-message="هل أنت متأكد من حذف هذا الرابط نهائياً؟"
                                            data-name="{{ $link->title_text }}"
                                            data-meta="#{{ $link->id }}"
                                            data-action="{{ route('dashboard.links.destroy', $link) }}"
                                            data-method="DELETE"
                                            data-btn-text="نعم، احذف"
                                            data-btn-class="btn-danger">
                                            <i class="ti ti-trash" data-bs-toggle="tooltip" data-bs-title="حذف"></i>
                                        </button>
                                    </div>
                                @else
                                    <div class="text-secondary small mb-2">غير مضاف بعد</div>
                                    <a href="{{ route('dashboard.links.create', ['type' => $slot['type']]) }}" class="btn btn-sm btn-outline-primary">إضافة</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title">أرقام التواصل</h3>
            <div class="card-subtitle">أرقام هاتف وواتساب إضافية للتواصل </div>
        </div>
        <a href="{{ route('dashboard.links.create', ['context' => 'contact-number', 'type' => 'phone']) }}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus ms-2"></i>
            إضافة رقم تواصل
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-vcenter">
                <thead>
                <tr>
                    <th>ترتيب</th>
                    <th>النوع</th>
                    <th>العنوان الظاهر</th>
                    <th>الرقم</th>
                    <th>النقرات</th>
                    <th>إجراءات</th>
                </tr>
                </thead>
                <tbody class="js-links-tbody">
                @forelse($contactNumberLinks as $link)
                    <tr data-id="{{ $link->id }}">
                        <td class="text-center">
                            <span class="js-drag-handle cursor-pointer">
                                <i class="ti ti-grip-vertical"></i>
                                <span class="small js-order-text">{{ $link->display_order }}</span>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-primary-lt">
                                {{ $link->type === 'phone' ? 'هاتف' : 'واتساب' }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $link->title_text }}</div>
                        </td>
                        <td>
                            <a href="{{ $link->url }}" target="_blank" rel="noopener" class="text-reset text-decoration-underline small" dir="ltr">
                                {{ $displayUrl($link) }}
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-secondary-lt">{{ $link->clicks }}</span>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('dashboard.links.edit', $link) }}" class="text-primary">
                                    <i class="ti ti-edit"></i>
                                </a>
                                <button
                                    type="button"
                                    class="js-icon-action text-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmDeleteModal"
                                    data-title="حذف الرقم"
                                    data-message="هل أنت متأكد من حذف هذا الرقم نهائيًا؟"
                                    data-name="{{ $link->title_text }}"
                                    data-meta="#{{ $link->id }}"
                                    data-action="{{ route('dashboard.links.destroy', $link) }}"
                                    data-method="DELETE"
                                    data-btn-text="نعم، احذف"
                                    data-btn-class="btn-danger">
                                    <i class="ti ti-x" data-bs-toggle="tooltip" data-bs-title="حذف"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            لا توجد أرقام تواصل إضافية بعد 🌱
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title">روابط مخصصة</h3>
            <div class="card-subtitle">يمكنك إضافة أكثر من رابط مخصص وترتيبها بالسحب والإفلات.</div>
        </div>
        <a href="{{ route('dashboard.links.create', ['type' => 'custom']) }}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus ms-2"></i>
            إضافة رابط مخصص
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-vcenter">
                <thead>
                <tr>
                    <th>ترتيب</th>
                    <th>الأيقونة</th>
                    <th>العنوان</th>
                    <th>الرابط</th>
                    <th>النقرات</th>
                    <th>إجراءات</th>
                </tr>
                </thead>

                <tbody id="links-tbody" class="js-links-tbody">
                @forelse($customLinks as $link)
                <tr data-id="{{ $link->id }}">
                    <td class="text-center">
                        <span class="js-drag-handle cursor-pointer">
                            <i class="ti ti-grip-vertical"></i>
                            <span class="small js-order-text">{{ $link->display_order }}</span>
                        </span>
                    </td>

                    <td>
                        <span class="avatar avatar-sm bg-primary-lt text-primary">
                            <i class="{{ $link->icon ?: 'ti ti-link' }}"></i>
                        </span>
                    </td>

                    <td>
                        <div class="fw-bold">{{ $link->title_text }}</div>
                    </td>

                    <td>
                        <a href="{{ $link->url }}" target="_blank" rel="noopener" class="text-reset text-decoration-underline small" dir="ltr">
                            {{ Str::limit($link->url, 55) }}
                        </a>
                    </td>

                    <td>
                        <span class="badge bg-secondary-lt">{{ $link->clicks }}</span>
                    </td>

                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('dashboard.links.edit', $link) }}" class="text-primary">
                                <i class="ti ti-edit"></i>
                            </a>

                            <button
                                type="button"
                                class="js-icon-action text-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#confirmDeleteModal"
                                data-title="حذف الرابط"
                                data-message="هل أنت متأكد من حذف هذا الرابط نهائياً؟"
                                data-name="{{ $link->title_text }}"
                                data-meta="#{{ $link->id }}"
                                data-action="{{ route('dashboard.links.destroy', $link) }}"
                                data-method="DELETE"
                                data-btn-text="نعم، احذف"
                                data-btn-class="btn-danger">
                                <i class="ti ti-x"
                                   data-bs-toggle="tooltip"
                                   data-bs-title="حذف"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        لا توجد روابط مخصصة بعد 🌱
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let dragRow = null;

document.querySelectorAll('.js-links-tbody').forEach(tbody => tbody.querySelectorAll('tr[data-id]').forEach(row => {
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
        if (!dragRow) return;

        dragRow.classList.remove('table-active');

        const items = [];

        tbody.querySelectorAll('tr[data-id]').forEach((linkRow, index) => {
            items.push({
                id: linkRow.dataset.id,
                order: index
            });

            linkRow.querySelector('.js-order-text').textContent = index;
        });

        fetch("{{ route('dashboard.links.reorder') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ items })
        });
    });
}));
</script>
@endpush
