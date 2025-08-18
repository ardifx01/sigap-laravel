<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="layout-menu-fixed" data-base-url="{{url('/')}}" data-framework="laravel">
  <head>
    @include('partials.head')
  </head>

  <body>

    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">

        <!-- Layout Content -->
        <x-layouts.menu.vertical :title="$title ?? null"></x-layouts.menu.vertical>
        <!--/ Layout Content -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->
          <x-layouts.navbar.default :title="$title ?? null"></x-layouts.navbar.default>
          <!--/ Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->
            <div class="container-xxl flex-grow-1 container-p-y">
              {{ $slot }}
            </div>
            <!-- / Content -->

            <!-- Footer -->
            <x-layouts.footer.default :title="$title ?? null"></x-layouts.footer.default>
            <!--/ Footer -->
            <div class="content-backdrop fade"></div>
            <!-- / Content wrapper -->
          </div>
        </div>
        <!-- / Layout page -->
      </div>
    </div>

    <!-- Include Scripts -->
    @include('partials.scripts')
    <!-- / Include Scripts -->

    <script>
        function initializeHamburgerToggle() {
            const hamburger = document.getElementById('hamburger-menu-toggle');
            if (hamburger) {
                hamburger.onclick = function (e) {
                    e.preventDefault();
                    document.documentElement.classList.toggle('layout-menu-expanded');
                };
            }
        }

        // Global TomSelect helper function
        window.initTomSelectWithNavigation = function(elementId, model, componentId = null) {
            const el = document.getElementById(elementId);
            if (!el) return null;

            // Check if TomSelect is already initialized on this element
            if (el.tomselect) {
                // Destroy existing instance
                el.tomselect.destroy();
            }

            const tomSelect = new TomSelect(el, {
                create: false,
                sortField: { field: "text", direction: "asc" },
                placeholder: el.getAttribute('placeholder') || 'Pilih...',
                onChange: (value) => {
                    if (window.Livewire && window.Livewire.find) {
                        const component = componentId ?
                            window.Livewire.find(componentId) :
                            window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
                        if (component) {
                            component.set(model, value);
                        }
                    }
                }
            });
            return tomSelect;
        };

        document.addEventListener('DOMContentLoaded', initializeHamburgerToggle);
        document.addEventListener('livewire:navigated', initializeHamburgerToggle);
    </script>
  </body>
</html>
