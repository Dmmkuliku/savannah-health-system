{{-- Decorative savannah scene: grassland, acacia trees, subtle snake silhouette --}}
<svg {{ $attributes->merge(['class' => 'block w-full', 'viewBox' => '0 0 720 420', 'xmlns' => 'http://www.w3.org/2000/svg', 'aria-hidden' => 'true', 'preserveAspectRatio' => 'xMidYMax meet']) }}>
    <defs>
        <linearGradient id="skyMint" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#98E8C8" stop-opacity=".35"/>
            <stop offset="55%" stop-color="#1E7A5C" stop-opacity=".15"/>
            <stop offset="100%" stop-color="#0C2C23" stop-opacity="0"/>
        </linearGradient>
        <linearGradient id="grass" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#5FD4A8" stop-opacity=".45"/>
            <stop offset="100%" stop-color="#0C2C23" stop-opacity=".9"/>
        </linearGradient>
    </defs>

    <rect width="720" height="420" fill="url(#skyMint)"/>

    <path d="M0 250 C120 210 180 270 300 240 C420 210 480 260 720 230 L720 420 L0 420 Z" fill="#194F3E" opacity=".55"/>
    <path d="M0 290 C160 250 240 310 380 280 C520 250 600 300 720 275 L720 420 L0 420 Z" fill="url(#grass)"/>

    <g class="animate-sway" style="transform-origin: 140px 340px">
        <path d="M140 340 V250" stroke="#5C4632" stroke-width="6" stroke-linecap="round"/>
        <path d="M140 290 l-18 12 M140 270 l16 14 M140 255 l-12 10" stroke="#5C4632" stroke-width="3" stroke-linecap="round"/>
        <ellipse cx="118" cy="232" rx="42" ry="22" fill="#36BA8A" opacity=".85"/>
        <ellipse cx="158" cy="226" rx="38" ry="20" fill="#98E8C8" opacity=".75"/>
        <ellipse cx="140" cy="214" rx="34" ry="18" fill="#5FD4A8"/>
    </g>

    <g class="animate-sway" style="transform-origin: 560px 320px; animation-delay: -2.5s">
        <path d="M560 350 V230" stroke="#5C4632" stroke-width="7" stroke-linecap="round"/>
        <path d="M560 280 l-22 14 M560 255 l20 16" stroke="#5C4632" stroke-width="3.5" stroke-linecap="round"/>
        <ellipse cx="530" cy="208" rx="52" ry="26" fill="#259971" opacity=".9"/>
        <ellipse cx="580" cy="200" rx="48" ry="24" fill="#98E8C8" opacity=".8"/>
        <ellipse cx="556" cy="186" rx="40" ry="20" fill="#36BA8A"/>
    </g>

    <g>
        <path d="M360 360 V300" stroke="#5C4632" stroke-width="4" stroke-linecap="round"/>
        <ellipse cx="348" cy="288" rx="28" ry="14" fill="#5FD4A8" opacity=".8"/>
        <ellipse cx="372" cy="284" rx="24" ry="12" fill="#98E8C8" opacity=".7"/>
    </g>

    <g class="animate-drift" opacity=".7">
        <path d="M210 370 C250 350 280 380 320 360 C355 342 390 355 430 348 C460 342 485 355 510 350"
              fill="none" stroke="#0C2C23" stroke-width="5" stroke-linecap="round"/>
        <circle cx="514" cy="349" r="4" fill="#0C2C23"/>
        <path d="M508 346 l8 -6 M508 352 l8 4" stroke="#98E8C8" stroke-width="1.5" stroke-linecap="round"/>
    </g>

    <g stroke="#98E8C8" stroke-width="2" stroke-linecap="round" opacity=".55">
        <path d="M40 390 l4 -18 M48 390 l-2 -14 M56 390 l6 -16"/>
        <path d="M640 395 l3 -16 M648 395 l-1 -12 M656 395 l5 -15"/>
        <path d="M280 400 l3 -12 M288 400 l-2 -10"/>
    </g>
</svg>
