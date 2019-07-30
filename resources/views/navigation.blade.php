{{--<li class="leading-tight mb-4 text-base">--}}
<router-link :to="{ name: 'calendar' }" class="text-white text-justify no-underline dim">
    @if(File::exists('icons/calendar.svg'))
        {!! File::get('icons/calendar.svg') !!}
    @else
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="1" y="3" width="16" height="16" rx="2" stroke="white" stroke-width="2" stroke-linecap="round"
                  stroke-linejoin="round"/>
            <path d="M12.5 1V5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M5.5 1V5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M1 8.5H17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    @endif
    <span style="margin-left: 5px;"></span>
    {{ 'Календарь' }}
</router-link>
{{--</li>--}}