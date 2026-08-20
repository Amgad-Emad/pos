{{-- قائمة اقتراحات البحث — تعتمد على حالة مكوّن searchSuggest في العنصر الأب --}}
<div class="card search-suggest-menu position-absolute w-100 shadow mt-1" style="z-index: 1055;"
     x-show="isOpen" x-cloak @click.outside="close()">
    <ul class="list-group list-group-flush" role="listbox">
        <template x-for="(item, index) in items" :key="index">
            <li class="list-group-item search-suggest-item d-flex justify-content-between align-items-center gap-2"
                :class="{ 'is-active': index === active }" :id="`${id}-option-${index}`"
                role="option" :aria-selected="index === active"
                @mouseenter="active = index" @mousedown.prevent="pick(item)">
                <span class="text-truncate" x-html="mark(item.label)"></span>
                <span class="text-muted small text-nowrap" x-show="item.meta" x-text="item.meta"></span>
            </li>
        </template>
        <li class="list-group-item text-muted text-center small" x-show="loading && items.length === 0">
            {{ __('messages.actions.searching') }}
        </li>
        <li class="list-group-item text-muted text-center small" x-show="! loading && searched && items.length === 0">
            {{ __('messages.table.no_results') }}
        </li>
    </ul>
</div>
