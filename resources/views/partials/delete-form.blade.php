{{-- نموذج حذف موحد: يتطلب $action --}}
<form method="POST" action="{{ $action }}" class="d-inline"
      onsubmit="return confirm(@js(__('messages.actions.confirm_delete_text')))">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-soft-danger btn-icon" title="{{ __('messages.actions.delete') }}">
        <i data-lucide="trash-2" style="width:16px;height:16px;"></i>
    </button>
</form>
