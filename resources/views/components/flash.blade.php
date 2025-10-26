@props(['type' => 'success', 'message' => null, 'autohide' => 6000])

@if($message)
    <div data-flash class="max-w-7xl mx-auto mt-20 px-6">
        <div class="{{ $type === 'success' ? 'bg-green-800 text-green-100' : 'bg-yellow-900 text-yellow-100' }} rounded p-3 shadow flex items-center justify-between">
            <div class="text-sm">{{ $message }}</div>
            <button data-flash-dismiss class="ml-4 text-current hover:text-white focus:outline-none" aria-label="Dismiss">&times;</button>
        </div>
    </div>

    <script>
        (function(){
            const container = document.querySelector('[data-flash]');
            const btn = container && container.querySelector('[data-flash-dismiss]');
            if(!container) return;
            if(btn) btn.addEventListener('click', ()=> container.remove(), {passive:true});
            const time = {{ (int) $autohide }};
            if(time > 0) setTimeout(()=>{ try{ container.remove(); }catch(e){} }, time);
        })();
    </script>
@endif
