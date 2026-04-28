@php($currentTheme = request()->cookie('theme', 'light'))
<button type="button" class="btn btn-light btn-sm" id="theme-toggle"
        title="Ubah tema" data-current-theme="{{ $currentTheme }}">
    <i class="bi @if($currentTheme === 'dark') bi-sun-fill @else bi-moon-stars-fill @endif"></i>
</button>
