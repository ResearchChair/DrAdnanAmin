<x-filament-panels::page>
  @php
      $colors = $this->data ?? [];
      $accent = $colors['accent_color'] ?? '#5B2C6F';
      $secondary = $colors['secondary_color'] ?? '#C17AA8';
      $surface = $colors['surface_color'] ?? '#FFF9F5';
      $surfaceMuted = $colors['surface_muted_color'] ?? '#F5EBE8';
  @endphp

  <div
      wire:key="theme-preview-{{ md5($accent.$secondary.$surface.$surfaceMuted) }}"
      class="mb-6 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700"
  >
      <div class="flex h-14" style="background: linear-gradient(135deg, {{ $accent }} 0%, color-mix(in srgb, {{ $accent }} 70%, {{ $secondary }} 30%) 100%);">
          <div class="flex flex-1 items-center px-4">
              <span class="font-semibold text-white text-sm drop-shadow-sm">Hero &amp; header accent</span>
          </div>
          <div class="w-24 shrink-0" style="background: {{ $secondary }};" title="Secondary"></div>
      </div>
      <div class="grid grid-cols-2 text-xs">
          <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700" style="background: {{ $surface }};">
              <span class="font-medium text-gray-700 dark:text-gray-200">Background</span>
              <span class="mt-1 block font-mono text-gray-500">{{ $surface }}</span>
          </div>
          <div class="px-4 py-3 border-t border-l border-gray-200 dark:border-gray-700" style="background: {{ $surfaceMuted }};">
              <span class="font-medium text-gray-700 dark:text-gray-200">Muted background</span>
              <span class="mt-1 block font-mono text-gray-500">{{ $surfaceMuted }}</span>
          </div>
      </div>
  </div>

  <form wire:submit="save">
      {{ $this->form }}

      <div class="mt-6">
          <x-filament::button type="submit">Save Settings</x-filament::button>
      </div>
  </form>
</x-filament-panels::page>
