@props(['status'])
<span class="label {{ $status }}">{{ \App\Support\StatusLaporan::label($status) }}</span>
