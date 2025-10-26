{{-- Bootstrap 5 account dropdown partial --}}
<div class="dropdown">
    <a class="btn btn-sm btn-dark rounded-circle d-flex align-items-center justify-content-center" href="#" role="button" id="accountDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        {{-- small avatar icon; replace with user image if available --}}
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16" aria-hidden="true">
            <path d="M13.468 12.37C12.758 11.226 11.488 10.5 10 10.5H6c-1.488 0-2.758.726-3.468 1.87A6.987 6.987 0 0 0 8 15a6.987 6.987 0 0 0 5.468-2.63z"/>
            <path fill-rule="evenodd" d="M8 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
            <path fill-rule="evenodd" d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1z"/>
        </svg>
    </a>

    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
        <li>
            <a class="dropdown-item" href="{{ route('account') }}">About Us</a>
        </li>
        <li>
            {{-- Use a POST form with CSRF for secure logout; button styled as dropdown-item --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item">Sign out</button>
            </form>
        </li>
    </ul>
</div>
