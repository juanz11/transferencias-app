@component('mail::layout')
{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url'), 'appName' => mb_convert_encoding($appName ?? config('app.name'), 'UTF-8')])
{!! mb_convert_encoding($appName ?? config('app.name'), 'UTF-8') !!}
@endcomponent
@endslot

{{-- Body --}}
{!! mb_convert_encoding($slot, 'UTF-8') !!}

{{-- Subcopy --}}
@isset($subcopy)
@slot('subcopy')
@component('mail::subcopy')
{!! mb_convert_encoding($subcopy, 'UTF-8') !!}
@endcomponent
@endslot
@endisset

{{-- Footer --}}
@slot('footer')
@component('mail::footer')
{!! mb_convert_encoding('© ' . date('Y') . ' ' . ($appName ?? config('app.name')) . '. ' . trans('All rights reserved.'), 'UTF-8') !!}
@endcomponent
@endslot
@endcomponent
