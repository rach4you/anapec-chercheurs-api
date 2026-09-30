<!-- Sidebar partial for admin layout -->
<aside class="bg-gray-800 text-white w-64 min-h-screen">
    <div class="p-4">
        <h2 class="text-xl font-bold">ANAPEC Admin</h2>
    </div>
    <nav class="mt-5">
        <ul>
            <li><a href="{{ route('dashboard') }}" class="block px-4 py-2 hover:bg-gray-700">Dashboard</a></li>
            <li><a href="{{ route('admin.users') }}" class="block px-4 py-2 hover:bg-gray-700">Users</a></li>
            <li><a href="{{ route('admin.web-services') }}" class="block px-4 py-2 hover:bg-gray-700">Web Services</a></li>
            <li><a href="{{ route('admin.web-service-domains') }}" class="block px-4 py-2 hover:bg-gray-700">Domains</a></li>
        </ul>
    </nav>
</aside>