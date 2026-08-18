{{-- نافذة تكبير الصور: تُفتح عند الضغط على أي صورة تحمل الخاصية data-lightbox-src --}}
<div id="image-lightbox" class="image-lightbox" hidden>
    <button type="button" class="image-lightbox-close" data-lightbox-close
    aria-label="{{ __('messages.actions.close') }}">
        <i data-lucide="x" style="width:20px;height:20px;"></i>
    </button>
    <figure class="image-lightbox-figure">
        <img src="" alt="" id="image-lightbox-img">
        <figcaption id="image-lightbox-caption"></figcaption>
    </figure>
</div>

<style>
    /* صورة قابلة للتكبير */
    .zoomable {
        cursor: zoom-in;
        object-fit: cover;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .zoomable:hover {
        transform: scale(1.06);
        box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .18);
    }

    .image-lightbox {
        position: fixed;
        inset: 0;
        z-index: 1080;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: .75rem;
        padding: 2rem 1rem;
        background: rgba(0, 0, 0, .82);
        backdrop-filter: blur(2px);
        cursor: zoom-out;
    }
    .image-lightbox[hidden] { display: none; }

    .image-lightbox-figure {
        margin: 0;
        max-width: 100%;
        max-height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .5rem;
    }

    .image-lightbox-figure img {
        max-width: min(1600px, 96vw);
        max-height: 88vh;
        width: auto;
        height: auto;
        object-fit: contain;
        border-radius: .5rem;
        background: #fff;
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, .5);
        cursor: default;
    }

    .image-lightbox-figure figcaption {
        color: #fff;
        font-size: .95rem;
        text-align: center;
    }

    .image-lightbox-close {
        position: absolute;
        top: 1rem;
        inset-inline-end: 1rem;
        width: 2.5rem;
        height: 2.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 50%;
        color: #fff;
        background: rgba(255, 255, 255, .15);
        cursor: pointer;
    }
    .image-lightbox-close:hover { background: rgba(255, 255, 255, .3); }
</style>

<script>
    (function () {
        const overlay = document.getElementById('image-lightbox');
        const image = document.getElementById('image-lightbox-img');
        const caption = document.getElementById('image-lightbox-caption');

        if (!overlay) return;

        function open(src, label) {
            image.src = src;
            image.alt = label || '';
            caption.textContent = label || '';
            overlay.hidden = false;
            document.body.style.overflow = 'hidden';
        }

        function close() {
            overlay.hidden = true;
            image.src = '';
            document.body.style.overflow = '';
        }

        // مفوَّض على المستند حتى يعمل مع الصفوف المضافة ديناميكيًا.
        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('[data-lightbox-src]');

            if (trigger) {
                event.preventDefault();
                open(trigger.dataset.lightboxSrc, trigger.dataset.lightboxCaption || trigger.alt);
                return;
            }

            if (event.target.closest('[data-lightbox-close]') || event.target === overlay) {
                close();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !overlay.hidden) close();
        });
    })();
</script>
