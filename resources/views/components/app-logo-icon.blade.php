@php
$width = $width ?? '25';
@endphp
<span class="text-primary">
  <svg width="{{ $width }}" viewBox="0 0 25 42" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <defs>
      <path d="M12.5,2 L2,8 L2,28 L6,28 L6,32 L19,32 L19,28 L23,28 L23,8 L12.5,2 Z" id="warehouse-main"></path>
      <path d="M4,12 C3.2,13.5 3.5,15 5,16 C6.5,16.8 7.8,17.2 8.5,17.5 L12,18.5 L18,13 C16,9.5 14.5,7.8 12.5,2 C12.3,2.2 9.5,4 4,12 Z" id="warehouse-shadow"></path>
      <path d="M8,18.5 L11.5,20 C13,21 13.2,22.5 12,24.2 C10.8,25.9 9.8,26.8 8.8,27.2 C6.5,28.2 5.2,28.5 5.2,28.5 C5.2,28.5 4.2,27.8 2.5,26.2 C2.1,23.8 2.1,22.5 2.5,22.4 C3,22.2 4.3,20.2 4.6,20 C4.8,19.9 5.8,19.6 8,18.5 Z" id="warehouse-base"></path>
      <path d="M20,15 L24,20 C24.4,20.5 24.3,21.2 23.8,21.6 C23.6,21.8 23.3,21.9 23,21.9 L17,21.9 C16.4,21.9 16,21.5 16,20.9 C16,20.7 16.1,20.4 16.2,20 L18.2,15 C18.6,14.5 19.3,14.4 19.8,14.8 C19.9,14.9 19.9,15 20,15 Z" id="arrow-integration"></path>
    </defs>
    <g id="sigap-icon" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
      <g id="SIGAP-Logo" transform="translate(0.000000, 0.000000)">
        <g id="Warehouse-Icon" transform="translate(0.000000, 5.000000)">
          <g id="Main-Structure" transform="translate(0.000000, 0.000000)">
            <mask id="mask-warehouse" fill="white">
              <use xlink:href="#warehouse-main"></use>
            </mask>
            <use fill="#3498db" xlink:href="#warehouse-main"></use>
            <g id="Warehouse-Details" mask="url(#mask-warehouse)">
              <use fill="#2980b9" xlink:href="#warehouse-shadow"></use>
              <use fill-opacity="0.3" fill="#FFFFFF" xlink:href="#warehouse-shadow"></use>
            </g>
            <g id="Warehouse-Base" mask="url(#mask-warehouse)">
              <use fill="#2980b9" xlink:href="#warehouse-base"></use>
              <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#warehouse-base"></use>
            </g>
            <!-- Warehouse doors and windows -->
            <rect x="8" y="22" width="3" height="5" fill="#FFFFFF" fill-opacity="0.8"/>
            <rect x="14" y="22" width="3" height="5" fill="#FFFFFF" fill-opacity="0.8"/>
            <rect x="6" y="14" width="2" height="2" fill="#FFFFFF" fill-opacity="0.6"/>
            <rect x="17" y="14" width="2" height="2" fill="#FFFFFF" fill-opacity="0.6"/>
          </g>
          <g id="Integration-Arrow" transform="translate(16.000000, 8.000000)">
            <use fill="#27ae60" xlink:href="#arrow-integration"></use>
            <use fill-opacity="0.3" fill="#FFFFFF" xlink:href="#arrow-integration"></use>
          </g>
          <!-- Sales indicator -->
          <circle cx="20" cy="35" r="3" fill="#e74c3c"/>
          <path d="M18.5 35 L21.5 35 M20 33.5 L20 36.5" stroke="#FFFFFF" stroke-width="1"/>
        </g>
        <!-- System status indicators -->
        <circle cx="3" cy="3" r="1.5" fill="#f39c12"/>
        <circle cx="22" cy="3" r="1.5" fill="#27ae60"/>
        <!-- Data flow lines -->
        <path d="M12.5 37 Q15 40 20 37" stroke="#95a5a6" stroke-width="1" stroke-dasharray="1,1" fill="none" opacity="0.6"/>
      </g>
    </g>
  </svg>
</span>
