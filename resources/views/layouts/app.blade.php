<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8eLT/An5PTDNPjjPMZX4WqU5YGCkmPLbG22P17R9GxytP+53EMyY00uwItS5ZX8Zeo+Vn1+/J8YV3j1Iw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}"><img src="{{ asset('logo/logo-white-250.png') }}" alt="Logo" style="height: 39px; width: auto;"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('transferencias.index') }}">Transferencias</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/transferencias/confirmados">Editar Transferencia</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('pedidos.index') }}">Pedidos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('transferencias.pedidos.create') }}">Crear Pedido</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('productos.*') ? 'active' : '' }}" href="{{ route('productos.index') }}">
                                <i class="fas fa-box me-2"></i>Productos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('visitadores.*') ? 'active' : '' }}" href="{{ route('visitadores.index') }}">
                                <i class="fas fa-users me-2"></i>Visitadores
                            </a>
                        </li>
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
                                <button type="submit" class="btn btn-link nav-link">Cerrar Sesión</button>
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
