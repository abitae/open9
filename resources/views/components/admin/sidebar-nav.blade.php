{{-- Orden: páginas del sitio → secciones (y cards con CRUD propio) --}}

<flux:sidebar.group :heading="__('Plataforma')" class="grid">
    <flux:sidebar.item icon="home" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>
        {{ __('Panel') }}
    </flux:sidebar.item>
</flux:sidebar.group>

<flux:sidebar.group
    :heading="__('Global del sitio')"
    expandable
    :expanded="request()->routeIs('admin.site-branding.*', 'admin.footer-*', 'admin.social-links.*', 'admin.ai-chat.*')"
    class="grid"
>
    @can('site-branding.view')
        <flux:sidebar.item icon="sparkles" :href="route('admin.site-branding.index')" :current="request()->routeIs('admin.site-branding.*')" wire:navigate>{{ __('Identidad y marca') }}</flux:sidebar.item>
    @endcan
    @can('footer-links.view')
        <flux:sidebar.item icon="rectangle-group" :href="route('admin.footer-link-groups.index')" :current="request()->routeIs('admin.footer-link-groups.*')" wire:navigate>{{ __('Footer — grupos') }}</flux:sidebar.item>
        <flux:sidebar.item icon="link" :href="route('admin.footer-links.index')" :current="request()->routeIs('admin.footer-links.*')" wire:navigate>{{ __('Footer — enlaces') }}</flux:sidebar.item>
    @endcan
    @can('social-links.view')
        <flux:sidebar.item icon="share" :href="route('admin.social-links.index')" :current="request()->routeIs('admin.social-links.*')" wire:navigate>{{ __('Redes sociales') }}</flux:sidebar.item>
    @endcan
    @can('ai-chat.view')
        <flux:sidebar.item icon="chat-bubble-left-right" :href="route('admin.ai-chat.index')" :current="request()->routeIs('admin.ai-chat.*')" wire:navigate>{{ __('Chat IA') }}</flux:sidebar.item>
    @endcan
</flux:sidebar.group>

<flux:sidebar.group
    :heading="__('Página Inicio')"
    expandable
    :expanded="request()->routeIs(
        'admin.home-hero-panel.*',
        'admin.home-hero-showcase.*',
        'admin.home-section-headers.*',
        'admin.home-stats.*',
        'admin.home-feature-cards.*',
        'admin.home-workflow-steps.*',
        'admin.home-quick-links.*',
        'admin.home-pricing-plans.*',
        'admin.testimonials.*'
    )"
    class="grid"
>
  @can('home-hero-panel.view')
      <flux:sidebar.item icon="presentation-chart-line" :href="route('admin.home-hero-panel.index')" :current="request()->routeIs('admin.home-hero-panel.*')" wire:navigate>{{ __('Hero — card principal') }}</flux:sidebar.item>
  @endcan
  @can('home-hero-showcase.view')
      <flux:sidebar.item icon="photo" :href="route('admin.home-hero-showcase.index')" :current="request()->routeIs('admin.home-hero-showcase.*')" wire:navigate>{{ __('Hero — cards lateral') }}</flux:sidebar.item>
  @endcan
  @can('home-section-headers.view')
      <flux:sidebar.item icon="bars-3-bottom-left" :href="route('admin.home-section-headers.index')" :current="request()->routeIs('admin.home-section-headers.*')" wire:navigate>{{ __('Encabezados de secciones') }}</flux:sidebar.item>
  @endcan
  @can('home-stats.view')
      <flux:sidebar.item icon="chart-bar" :href="route('admin.home-stats.index')" :current="request()->routeIs('admin.home-stats.*')" wire:navigate>{{ __('Sección métricas') }}</flux:sidebar.item>
  @endcan
  @can('home-feature-cards.view')
      <flux:sidebar.item icon="squares-2x2" :href="route('admin.home-feature-cards.index')" :current="request()->routeIs('admin.home-feature-cards.*')" wire:navigate>{{ __('Sección plataforma — cards') }}</flux:sidebar.item>
  @endcan
  @can('home-workflow-steps.view')
      <flux:sidebar.item icon="queue-list" :href="route('admin.home-workflow-steps.index')" :current="request()->routeIs('admin.home-workflow-steps.*')" wire:navigate>{{ __('Sección metodología') }}</flux:sidebar.item>
  @endcan
  @can('home-quick-links.view')
      <flux:sidebar.item icon="link" :href="route('admin.home-quick-links.index')" :current="request()->routeIs('admin.home-quick-links.*')" wire:navigate>{{ __('Sección enlaces rápidos — cards') }}</flux:sidebar.item>
  @endcan
  @can('testimonials.view')
      <flux:sidebar.item icon="chat-bubble-bottom-center-text" :href="route('admin.testimonials.index')" :current="request()->routeIs('admin.testimonials.*')" wire:navigate>{{ __('Sección testimonios — cards') }}</flux:sidebar.item>
  @endcan
  @can('home-pricing-plans.view')
      <flux:sidebar.item icon="currency-dollar" :href="route('admin.home-pricing-plans.index')" :current="request()->routeIs('admin.home-pricing-plans.*')" wire:navigate>{{ __('Sección precios — planes') }}</flux:sidebar.item>
  @endcan
</flux:sidebar.group>

<flux:sidebar.group
    :heading="__('Página Proyectos')"
    expandable
    :expanded="request()->routeIs('admin.projects.*', 'admin.project-categories.*')"
    class="grid"
>
    @can('projects.view')
        <flux:sidebar.item icon="folder-git-2" :href="route('admin.projects.index')" :current="request()->routeIs('admin.projects.*')" wire:navigate>{{ __('Proyectos — cards') }}</flux:sidebar.item>
    @endcan
    @can('project-categories.view')
        <flux:sidebar.item icon="tag" :href="route('admin.project-categories.index')" :current="request()->routeIs('admin.project-categories.*')" wire:navigate>{{ __('Proyectos — categorías') }}</flux:sidebar.item>
    @endcan
</flux:sidebar.group>

<flux:sidebar.group
    :heading="__('Página Servicios')"
    expandable
    :expanded="request()->routeIs('admin.services.*', 'admin.service-categories.*')"
    class="grid"
>
    @can('services.view')
        <flux:sidebar.item icon="wrench-screwdriver" :href="route('admin.services.index')" :current="request()->routeIs('admin.services.*')" wire:navigate>{{ __('Servicios — cards') }}</flux:sidebar.item>
    @endcan
    @can('service-categories.view')
        <flux:sidebar.item icon="tag" :href="route('admin.service-categories.index')" :current="request()->routeIs('admin.service-categories.*')" wire:navigate>{{ __('Servicios — categorías') }}</flux:sidebar.item>
    @endcan
</flux:sidebar.group>

<flux:sidebar.group
    :heading="__('Página Blog')"
    expandable
    :expanded="request()->routeIs('admin.blog.*', 'admin.blog-categories.*', 'admin.blog-tags.*')"
    class="grid"
>
    @can('blog.view')
        <flux:sidebar.item icon="newspaper" :href="route('admin.blog.index')" :current="request()->routeIs('admin.blog.*')" wire:navigate>{{ __('Artículos') }}</flux:sidebar.item>
    @endcan
    @can('blog-categories.view')
        <flux:sidebar.item icon="tag" :href="route('admin.blog-categories.index')" :current="request()->routeIs('admin.blog-categories.*')" wire:navigate>{{ __('Blog — categorías') }}</flux:sidebar.item>
    @endcan
    @can('blog-tags.view')
        <flux:sidebar.item icon="hashtag" :href="route('admin.blog-tags.index')" :current="request()->routeIs('admin.blog-tags.*')" wire:navigate>{{ __('Blog — etiquetas') }}</flux:sidebar.item>
    @endcan
</flux:sidebar.group>

<flux:sidebar.group
    :heading="__('Página Tienda')"
    expandable
    :expanded="request()->routeIs('admin.products.*', 'admin.product-categories.*', 'admin.product-brands.*', 'admin.orders.*', 'admin.payment-settings.*', 'admin.clients.*', 'admin.social-login.*')"
    class="grid"
>
    @can('products.view')
        <flux:sidebar.item icon="shopping-bag" :href="route('admin.products.index')" :current="request()->routeIs('admin.products.*')" wire:navigate>{{ __('Productos — cards') }}</flux:sidebar.item>
    @endcan
    @can('product-categories.view')
        <flux:sidebar.item icon="tag" :href="route('admin.product-categories.index')" :current="request()->routeIs('admin.product-categories.*')" wire:navigate>{{ __('Tienda — categorías') }}</flux:sidebar.item>
    @endcan
    @can('product-brands.view')
        <flux:sidebar.item icon="building-storefront" :href="route('admin.product-brands.index')" :current="request()->routeIs('admin.product-brands.*')" wire:navigate>{{ __('Tienda — marcas') }}</flux:sidebar.item>
    @endcan
    @can('orders.view')
        <flux:sidebar.item icon="clipboard-document-list" :href="route('admin.orders.index')" :current="request()->routeIs('admin.orders.*')" wire:navigate>{{ __('Tienda — pedidos') }}</flux:sidebar.item>
    @endcan
    @can('clients.view')
        <flux:sidebar.item icon="users" :href="route('admin.clients.index')" :current="request()->routeIs('admin.clients.*')" wire:navigate>{{ __('Tienda — clientes') }}</flux:sidebar.item>
    @endcan
    @can('payment-settings.view')
        <flux:sidebar.item icon="credit-card" :href="route('admin.payment-settings.index')" :current="request()->routeIs('admin.payment-settings.*')" wire:navigate>{{ __('Tienda — pasarela de pagos') }}</flux:sidebar.item>
    @endcan
    @can('social-login.view')
        <flux:sidebar.item icon="key" :href="route('admin.social-login.index')" :current="request()->routeIs('admin.social-login.*')" wire:navigate>{{ __('Tienda — acceso Google') }}</flux:sidebar.item>
    @endcan
</flux:sidebar.group>

<flux:sidebar.group
    :heading="__('Página Contacto')"
    expandable
    :expanded="request()->routeIs('admin.contacts.*')"
    class="grid"
>
    @can('contacts.view')
        <flux:sidebar.item icon="envelope" :href="route('admin.contacts.index')" :current="request()->routeIs('admin.contacts.*')" wire:navigate>{{ __('Mensajes recibidos') }}</flux:sidebar.item>
    @endcan
</flux:sidebar.group>

<flux:sidebar.group
    :heading="__('Páginas legales')"
    expandable
    :expanded="request()->routeIs('admin.legal-pages.*')"
    class="grid"
>
    @can('legal-pages.view')
        <flux:sidebar.item icon="document-text" :href="route('admin.legal-pages.index')" :current="request()->routeIs('admin.legal-pages.*')" wire:navigate>{{ __('Contenido legal') }}</flux:sidebar.item>
    @endcan
</flux:sidebar.group>

<flux:sidebar.group
    :heading="__('Academia')"
    expandable
    :expanded="request()->routeIs('admin.courses.*', 'admin.course-categories.*', 'admin.instructors.*', 'admin.course-modules.*', 'admin.course-lessons.*')"
    class="grid"
>
    @can('courses.view')
        <flux:sidebar.item icon="academic-cap" :href="route('admin.courses.index')" :current="request()->routeIs('admin.courses.*')" wire:navigate>{{ __('Cursos') }}</flux:sidebar.item>
    @endcan
    @can('course-modules.view')
        <flux:sidebar.item icon="rectangle-stack" :href="route('admin.course-modules.index')" :current="request()->routeIs('admin.course-modules.*')" wire:navigate>{{ __('Módulos') }}</flux:sidebar.item>
    @endcan
    @can('course-lessons.view')
        <flux:sidebar.item icon="play-circle" :href="route('admin.course-lessons.index')" :current="request()->routeIs('admin.course-lessons.*')" wire:navigate>{{ __('Lecciones') }}</flux:sidebar.item>
    @endcan
    @can('course-categories.view')
        <flux:sidebar.item icon="tag" :href="route('admin.course-categories.index')" :current="request()->routeIs('admin.course-categories.*')" wire:navigate>{{ __('Categorías') }}</flux:sidebar.item>
    @endcan
    @can('instructors.view')
        <flux:sidebar.item icon="user-group" :href="route('admin.instructors.index')" :current="request()->routeIs('admin.instructors.*')" wire:navigate>{{ __('Instructores') }}</flux:sidebar.item>
    @endcan
</flux:sidebar.group>

<flux:sidebar.group
    :heading="__('Operaciones')"
    expandable
    :expanded="request()->routeIs('admin.enrollments.*', 'admin.payments.*', 'admin.certificates.*', 'admin.newsletter.*')"
    class="grid"
>
    @can('enrollments.view')
        <flux:sidebar.item icon="user-plus" :href="route('admin.enrollments.index')" :current="request()->routeIs('admin.enrollments.*')" wire:navigate>{{ __('Inscripciones') }}</flux:sidebar.item>
    @endcan
    @can('payments.view')
        <flux:sidebar.item icon="credit-card" :href="route('admin.payments.index')" :current="request()->routeIs('admin.payments.*')" wire:navigate>{{ __('Pagos') }}</flux:sidebar.item>
    @endcan
    @can('certificates.view')
        <flux:sidebar.item icon="identification" :href="route('admin.certificates.index')" :current="request()->routeIs('admin.certificates.*')" wire:navigate>{{ __('Certificados') }}</flux:sidebar.item>
    @endcan
    @can('newsletter.view')
        <flux:sidebar.item icon="megaphone" :href="route('admin.newsletter.index')" :current="request()->routeIs('admin.newsletter.*')" wire:navigate>{{ __('Boletín') }}</flux:sidebar.item>
    @endcan
</flux:sidebar.group>

<flux:sidebar.group
    :heading="__('Acceso')"
    expandable
    :expanded="request()->routeIs('admin.users.*', 'admin.roles.*', 'admin.permissions.*')"
    class="grid"
>
    @can('users.view')
        <flux:sidebar.item icon="users" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.*')" wire:navigate>{{ __('Usuarios') }}</flux:sidebar.item>
    @endcan
    @can('roles.view')
        <flux:sidebar.item icon="shield-check" :href="route('admin.roles.index')" :current="request()->routeIs('admin.roles.*')" wire:navigate>{{ __('Roles') }}</flux:sidebar.item>
    @endcan
    @can('permissions.view')
        <flux:sidebar.item icon="key" :href="route('admin.permissions.index')" :current="request()->routeIs('admin.permissions.*')" wire:navigate>{{ __('Permisos') }}</flux:sidebar.item>
    @endcan
</flux:sidebar.group>

<flux:sidebar.group
    :heading="__('Sistema')"
    expandable
    :expanded="request()->routeIs('admin.media.*', 'admin.settings.*', 'admin.storage-settings.*')"
    class="grid"
>
    @can('media.view')
        <flux:sidebar.item icon="photo" :href="route('admin.media.index')" :current="request()->routeIs('admin.media.*')" wire:navigate>{{ __('Archivos') }}</flux:sidebar.item>
    @endcan
    @can('storage-settings.view')
        <flux:sidebar.item icon="server" :href="route('admin.storage-settings.index')" :current="request()->routeIs('admin.storage-settings.*')" wire:navigate>{{ __('Almacenamiento') }}</flux:sidebar.item>
    @endcan
    @can('settings.view')
        <flux:sidebar.item icon="cog-6-tooth" :href="route('admin.settings.index')" :current="request()->routeIs('admin.settings.*')" wire:navigate>{{ __('Configuración') }}</flux:sidebar.item>
    @endcan
</flux:sidebar.group>
