<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @yield('styles')
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: rgb(68, 78, 98);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .navbar {
            background-color: rgba(30, 35, 45, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 1050;
        }

        .navbar .dropdown-menu {
            z-index: 1060;
        }
        
        .navbar-brand img {
            transition: transform 0.3s ease;
        }
        
        .navbar-brand img:hover {
            transform: scale(1.05);
        }
        
        .nav-link {
            color: #e0e0e0 !important;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem !important;
            border-radius: 8px;
            margin: 0 0.25rem;
        }
        
        .nav-link:hover {
            color: #ffffff !important;
            background-color: rgba(52, 152, 219, 0.2);
        }
        
        .nav-link.active {
            background-color: rgb(31, 69, 145);
            color: #ffffff !important;
        }

        .dropdown-menu-dark {
            background-color: rgba(30, 35, 45, 0.98);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .dropdown-menu-dark .dropdown-item.active,
        .dropdown-menu-dark .dropdown-item:active {
            background-color: rgb(31, 69, 145);
        }

        .dropdown-menu-dark .dropdown-header {
            color: #8ab4f8;
            font-weight: bold;
            font-size: 0.75rem;
            text-transform: uppercase;
        }
        
        .btn-link.nav-link {
            background: none;
            border: none;
        }
        
        main.container {
            max-width: 1200px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ url('/') }}"><img src="{{ asset('logo/logo-white-250.png') }}" alt="Logo" style="height: 39px; width: auto;"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    @auth
                        @if(auth()->user()->rol === 'admin')
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="menuHamburguesa" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-bars me-2"></i>Menú
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="menuHamburguesa">
                                    <li><h6 class="dropdown-header">Transferencias</h6></li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('transferencias.index') || request()->routeIs('transferencias.reporte') ? 'active' : '' }}" href="{{ route('transferencias.index') }}">
                                            <i class="fas fa-exchange-alt me-2"></i>Transferencias
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('transferencias.confirmados*') ? 'active' : '' }}" href="/transferencias/confirmados">
                                            <i class="fas fa-edit me-2"></i>Editar Transferencia
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('admin.pedidos.pendientes') ? 'active' : '' }}" href="{{ route('admin.pedidos.pendientes') }}">
                                            <i class="fas fa-tasks me-2"></i>Control de Transferencias
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Pedidos</h6></li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('pedidos.index') ? 'active' : '' }}" href="{{ route('pedidos.index') }}">
                                            <i class="fas fa-clipboard-list me-2"></i>Pedidos
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('transferencias.pedidos.*') ? 'active' : '' }}" href="{{ route('transferencias.pedidos.create') }}">
                                            <i class="fas fa-plus me-2"></i>Crear Pedido
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Gestión</h6></li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('productos.*') ? 'active' : '' }}" href="{{ route('productos.index') }}">
                                            <i class="fas fa-box me-2"></i>Productos
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('visitadores.*') ? 'active' : '' }}" href="{{ route('visitadores.index') }}">
                                            <i class="fas fa-users me-2"></i>Visitadores
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('clientes.*') ? 'active' : '' }}" href="{{ route('clientes.index') }}">
                                            <i class="fas fa-user-tie me-2"></i>Clientes
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('discount_rules.*') ? 'active' : '' }}" href="{{ route('discount_rules.index') }}">
                                            <i class="fas fa-percent me-2"></i>Descuentos
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Reportes</h6></li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('admin.estadisticas.*') ? 'active' : '' }}" href="{{ route('admin.estadisticas.ventas') }}">
                                            <i class="fas fa-chart-bar me-2"></i>Estadísticas de Ventas
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('admin.charlas.*') ? 'active' : '' }}" href="{{ route('admin.charlas.index') }}">
                                            <i class="fas fa-chalkboard-teacher me-2"></i>Charlas
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @elseif(auth()->user()->rol === 'visitador')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('visitador.home') ? 'active' : '' }}" href="{{ route('visitador.home') }}">
                                    Inicio visitador
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('visitador.pedidos.create') ? 'active' : '' }}" href="{{ route('visitador.pedidos.create') }}">
                                    Crear pedido
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('visitador.pedidos.reporte') ? 'active' : '' }}" href="{{ route('visitador.pedidos.reporte') }}">
                                    Mis pedidos pendientes
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>
                <ul class="navbar-nav">
                    @auth
                        <li class="nav-item">
                            <span class="nav-link">{{ Auth::user()->email }}</span>
                        </li>
                        <li class="nav-item">
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link">Cerrar Sesion</button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Iniciar Sesión</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
