@props(['error' => null])

@if($error)
        <div data-recaptcha-alert tabindex="-1" role="alert" aria-live="assertive"
            class="mt-3 p-3 rounded text-sm bg-yellow-900 text-yellow-100 border border-yellow-700 outline-none opacity-0 transform -translate-y-2 transition-all duration-300">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="font-semibold">{{ __('recaptcha.failed_title') }}</p>
                <p class="mt-1">{{ __('recaptcha.failed_message') }}</p>
                <p class="mt-1 text-xs text-yellow-200"><strong>{{ __('recaptcha.details_label') }}:</strong> {{ $error }}</p>
            </div>
            <div class="flex-shrink-0">
                <button type="button" class="text-yellow-200 hover:text-white focus:outline-none focus:ring-2 focus:ring-yellow-400 rounded p-1" aria-label="{{ __('recaptcha.close') }}" data-recaptcha-dismiss>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                        <path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 11-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <script>
        (function(){
            const el = document.querySelector('[data-recaptcha-alert]');
            if(!el) return;
            // show with animation
            requestAnimationFrame(()=>{
                el.classList.remove('opacity-0','-translate-y-2');
                el.classList.add('opacity-100','translate-y-0');
                // autofocus for screen readers
                setTimeout(()=>{
                    try{ document.activeElement && document.activeElement.blur(); }catch(e){}
                    el.focus({preventScroll:true});
                }, 60);
            });

            const btn = el.querySelector('[data-recaptcha-dismiss]');
            if(btn){
                btn.addEventListener('click', function(){
                    // fade out
                    el.classList.remove('opacity-100','translate-y-0');
                    el.classList.add('opacity-0','-translate-y-2');
                    setTimeout(()=>{ el.remove(); }, 300);
                }, {passive:true});
            }
        })();
    </script>
@endif
