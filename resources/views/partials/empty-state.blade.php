{{-- حالة فارغة موحدة: تقبل $message، و$icon اختياريًا، و$actionUrl/$actionLabel اختياريًا --}}
<div class="empty-state">
    <span class="empty-icon">
        <i data-lucide="{{ $icon ?? 'inbox' }}" style="width:26px;height:26px;"></i>
    </span>
    <p class="mb-2">{{ $message }}</p>
    @isset($actionUrl)
        <a href="{{ $actionUrl }}" class="btn btn-sm btn-soft-primary">
            <i data-lucide="plus" style="width:15px;height:15px;"></i>
            {{ $actionLabel }}
        </a>
    @endisset
</div>
