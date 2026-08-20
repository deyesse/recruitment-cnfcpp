@php
    $footerText = \App\Models\Setting::getFooterText();
@endphp
<footer style="margin-top: 2rem; margin-bottom: 1.5rem; text-align: center !important; font-size: 0.75rem; line-height: 1rem; color: #0284c7 !important; font-weight: 500; border-top: 1px solid rgba(226, 232, 240, 0.8); padding-top: 1rem; width: 100%; font-family: inherit;" dir="ltr">
    {!! $footerText !!}
</footer>
