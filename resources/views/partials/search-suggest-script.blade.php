{{-- مكوّن الاقتراحات الفورية: يُحمَّل مرة واحدة لكل صفحة مهما تكرر تضمين حقول البحث --}}
@once
    @push('styles')
        <style>
            [x-cloak] { display: none !important; }
        </style>
    @endpush

    @push('scripts')
        <script>
            function searchSuggest(config) {
                return {
                    id: `search-suggest-${Math.random().toString(36).slice(2, 9)}`,
                    term: config.term || '',
                    items: [],
                    active: -1,
                    searched: false,
                    loading: false,
                    opened: false,
                    request: null,

                    // يُستدعى مع كل حرف: يجلب الاقتراحات أو يغلق القائمة عند إفراغ الحقل.
                    onInput() {
                        this.active = -1;
                        const term = this.term.trim();

                        if (! config.url || term === '') {
                            this.close();
                            this.searched = false;
                            this.items = [];

                            return;
                        }

                        this.fetchItems(term);
                    },

                    async fetchItems(term) {
                        // إلغاء الطلب السابق حتى لا تتأخر نتيجة قديمة فتحلّ محل الأحدث.
                        if (this.request) this.request.abort();

                        const request = new AbortController();
                        this.request = request;
                        this.loading = true;
                        this.opened = true;

                        try {
                            const response = await fetch(`${config.url}?q=${encodeURIComponent(term)}`, {
                                headers: { 'Accept': 'application/json' },
                                signal: request.signal,
                            });

                            if (! response.ok) throw new Error(response.status);

                            this.items = await response.json();
                            this.searched = true;
                        } catch (error) {
                            if (error.name === 'AbortError') return;
                            this.items = [];
                            this.searched = false;
                        } finally {
                            if (this.request === request) this.loading = false;
                        }
                    },

                    onFocus() {
                        this.opened = true;
                        if (this.items.length === 0 && this.term.trim() !== '') this.onInput();
                    },

                    get isOpen() {
                        return this.opened && (this.loading || this.searched || this.items.length > 0);
                    },

                    close() {
                        this.opened = false;
                        this.active = -1;
                    },

                    move(step) {
                        if (this.items.length === 0) return;
                        this.opened = true;
                        this.active = (this.active + step + this.items.length) % this.items.length;
                    },

                    onEnter(event) {
                        if (this.isOpen && this.active >= 0 && this.items[this.active]) {
                            event.preventDefault();
                            this.pick(this.items[this.active]);
                        } else {
                            this.close();
                        }
                    },

                    // الاختيار: الانتقال لصفحة العنصر إن وُجدت، وإلا تصفية الجدول بقيمته.
                    pick(item) {
                        this.close();

                        if (config.onPick && config.onPick(item, this) === false) return;

                        if (item.url) {
                            window.location.href = item.url;

                            return;
                        }

                        this.term = item.value ?? item.label;
                        // الإرسال بعد مزامنة قيمة الحقل مع الحالة، وإلا أُرسل النص القديم.
                        this.$nextTick(() => this.$el.closest('form')?.submit());
                    },

                    // إبراز الجزء المطابق لما كُتب داخل نص الاقتراح.
                    mark(text) {
                        const value = String(text ?? '');
                        const term = this.term.trim();

                        if (term === '') return this.escape(value);

                        const index = value.toLowerCase().indexOf(term.toLowerCase());

                        if (index === -1) return this.escape(value);

                        return this.escape(value.slice(0, index))
                            + '<mark class="p-0 bg-transparent fw-bold">'
                            + this.escape(value.slice(index, index + term.length))
                            + '</mark>'
                            + this.escape(value.slice(index + term.length));
                    },

                    escape(text) {
                        const element = document.createElement('span');
                        element.textContent = text;

                        return element.innerHTML;
                    },
                };
            }
        </script>
    @endpush
@endonce
