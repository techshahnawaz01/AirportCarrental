@props(['href', 'icon', 'active' => false, 'badge' => null])
<a href="{{ $href }}" @if ($active) aria-current="page" @endif
   @class([
       'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
       'bg-primary text-white shadow-sm' => $active,
       'text-slate-300 hover:bg-white/5 hover:text-white' => ! $active,
   ])>
    <x-icon :name="$icon" @class(['size-5 shrink-0', 'text-white' => $active, 'text-slate-400 group-hover:text-white' => ! $active]) />
    <span class="flex-1 truncate">{{ $slot }}</span>
    @if ($badge)
        <span @class(['rounded-full px-2 py-0.5 text-[11px] font-semibold', 'bg-white/20 text-white' => $active, 'bg-accent text-secondary' => ! $active])>{{ $badge }}</span>
    @endif
</a>
