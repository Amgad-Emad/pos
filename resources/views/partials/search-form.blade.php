{{-- نموذج بحث موحد مع اقتراحات فورية: يقبل $placeholder و$suggestType اختياريًا --}}
@php($suggestType = $suggestType ?? \Illuminate\Support\Str::before(request()->route()?->getName() ?? '', '.'))
@php($suggestUrl = in_array($suggestType, \App\Http\Controllers\SearchSuggestionController::TYPES, true)
    ? route('search.suggestions', $suggestType)
    : null)

<form method="GET" class="d-flex gap-2" role="search"
      x-data="searchSuggest({ url: @js($suggestUrl), term: @js((string) request('q')) })">
    <div class="position-relative" style="width: 260px; max-width: 100%;">
        <input type="search" name="q" x-model="term" autocomplete="off" role="combobox"
               class="form-control form-control-sm"
               placeholder="{{ $placeholder ?? __('messages.actions.search') }}"
               aria-autocomplete="list" :aria-expanded="isOpen"
               :aria-activedescendant="active >= 0 ? `${id}-option-${active}` : null"
               @input.debounce.150ms="onInput()"
               @focus="onFocus()"
               @keydown.arrow-down.prevent="move(1)"
               @keydown.arrow-up.prevent="move(-1)"
               @keydown.enter="onEnter($event)"
               @keydown.escape="close()">

        @include('partials.search-suggest-menu')
    </div>

    <button type="submit" class="btn btn-sm btn-soft-primary">{{ __('messages.actions.search') }}</button>
    @if (request('q'))
        <a href="{{ url()->current() }}" class="btn btn-sm btn-soft-secondary">{{ __('messages.actions.reset') }}</a>
    @endif
</form>

@include('partials.search-suggest-script')
