@props([
    'label' => null,
    'name' => '',
    'error' => null,
    'type' => 'text',
])

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $name }}" class="block text-xs font-semibold text-slate-300 sepia:text-sepia-text">
            {{ $label }}
        </label>
    @endif

    <input type="{{ $type }}"
           name="{{ $name }}"
           id="{{ $name }}"
           @if($error)
               aria-invalid="true"
               aria-describedby="{{ $name }}-error"
           @endif
           {{ $attributes->merge([
               'class' => 'w-full rounded-xl bg-slate-950/60 sepia:bg-sepia-100 border ' .
                          ($error ? 'border-rose-500 focus-visible:ring-rose-500' : 'border-slate-800 sepia:border-sepia-border focus:border-indigo-500 focus-visible:ring-indigo-500') .
                          ' px-3.5 py-2.5 text-sm text-white sepia:text-sepia-text placeholder-slate-500 focus-visible:outline-none focus-visible:ring-2 transition-colors'
           ]) }}>

    @if($error)
        <p id="{{ $name }}-error" role="alert" class="text-xs text-rose-400 sepia:text-rose-700 font-medium">{{ $error }}</p>
    @endif
</div>
