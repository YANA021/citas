<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas - Cliente</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(45deg, #4facfe 0%, #1be9f4 100%);
            --success-gradient: linear-gradient(45deg, #3dd26e 0%, #35ebc9 100%);
            --warning-gradient: linear-gradient(45deg, #fa709a 0%, #fee140 100%);
            --info-gradient: linear-gradient(45deg, #a8edea 0%, #fed6e3 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text-primary: #333;
            --text-secondary: #666;
            --shadow-soft: 0 8px 32px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 15px 35px rgba(0, 0, 0, 0.15);
            --border-radius: 0.75rem;
            --border-radius-lg: 1rem;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(180deg, #bbadfd, #5b21b6, #452383);
            min-height: 100vh;
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        /* Partículas flotantes de fondo */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(circle at 20% 80%, rgba(102, 126, 234, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(118, 75, 162, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(250, 112, 154, 0.05) 0%, transparent 50%);
            z-index: -1;
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            33% {
                transform: translate(30px, -30px) rotate(120deg);
            }

            66% {
                transform: translate(-20px, 20px) rotate(240deg);
            }
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-soft);
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 2.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header p {
            color: var(--text-secondary);
            margin-top: 10px;
            font-size: 1.1rem;
        }

        /* Navegación entre vistas */
        .view-nav {
            display: flex;
            justify-content: center;
            margin-bottom: 25px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius-lg);
            padding: 10px;
            box-shadow: var(--shadow-soft);
            gap: 10px;
        }

        .nav-btn {
            padding: 12px 24px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .nav-btn.active {
            background: var(--primary-gradient);
            color: white;
            box-shadow: var(--shadow-soft);
        }

        .nav-btn:not(.active) {
            background: transparent;
            color: var(--text-secondary);
        }

        .nav-btn:not(.active):hover {
            background: rgba(102, 126, 234, 0.1);
            color: var(--text-primary);
        }

        /* Botón flotante para nueva cita */
        .fab-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: white;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: var(--shadow-hover);
            transition: var(--transition);
            z-index: 1000;
        }

        .fab-button:hover {
            transform: scale(1.1);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
        }

        .filters-section {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-soft);
            padding: 25px;
            margin-bottom: 30px;
        }

        /* Contadores específicos para próximas citas */
        .counters-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.3);
            padding: 20px;
            border-radius: var(--border-radius);
        }

        .counter-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            min-width: 120px;
        }

        .counter-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .counter-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-align: center;
        }

        .counter-total .counter-value {
            color: #667eea;
        }

        .counter-pendiente .counter-value {
            color: #ef6c00;
        }

        .counter-confirmada .counter-value {
            color: #0277bd;
        }

        .counter-en_proceso .counter-value {
            color: #6a1b9a;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-group select,
        .filter-group input {
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            background: white;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
            box-shadow: var(--shadow-soft);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: var(--text-primary);
            border: 2px solid #e1e5e9;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            transform: translateY(-1px);
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 0.9rem;
        }

        .btn-warning {
            background: var(--warning-gradient);
            color: white;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #dc3545;
            color: #dc3545;
        }

        .btn-outline:hover {
            background: #dc3545;
            color: white;
        }

        .citas-grid {
            display: grid;
            gap: 20px;
        }

        .cita-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-soft);
            padding: 25px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .cita-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(79, 172, 254, 0.1));
            opacity: 0.1;
            transition: all 0.3s ease;
        }

        .cita-card:hover::before {
            width: 100%;
        }

        .cita-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        /* COLORES DE BORDE PARA PRÓXIMAS CITAS */
        .cita-card.pendiente {
            border-left: 5px solid #4facfe !important;
        }

        .cita-card.confirmada,
        .cita-card.confirmado {
            border-left: 5px solid #66bb6a !important;
        }

        .cita-card.en_proceso,
        .cita-card.en-proceso {
            border-left: 5px solid #1b5e20 !important;
        }

        /* Estilos para citas urgentes */
        .cita-card.urgent {
            border-left: 5px solid #dc3545 !important;
            animation: pulseBorder 2s infinite;
        }

        @keyframes pulseBorder {
            0% {
                border-left-color: #dc3545;
            }

            50% {
                border-left-color: #ff6b6b;
            }

            100% {
                border-left-color: #dc3545;
            }
        }

        .cita-card.coming-soon {
            border-left: 5px solid #fd7e14 !important;
        }

        .cita-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .cita-date-time {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .date-badge {
            color: white;
            padding: 15px;
            border-radius: var(--border-radius);
            text-align: center;
            min-width: 80px;
            box-shadow: var(--shadow-soft);
            position: relative;
        }

        /* COLORES DE BADGE PARA PRÓXIMAS CITAS */
        .date-badge.pendiente {
            background: var(--secondary-gradient) !important;
        }

        .date-badge.confirmada,
        .date-badge.confirmado {
            background: linear-gradient(135deg, #81c784, #66bb6a) !important;
        }

        .date-badge.en_proceso,
        .date-badge.en-proceso {
            background: linear-gradient(135deg, #2e7d32, #1b5e20) !important;
        }

        .date-badge .day {
            display: block;
            font-size: 1.8rem;
            font-weight: 700;
            line-height: 1;
        }

        .date-badge .month {
            display: block;
            font-size: 0.9rem;
            text-transform: uppercase;
            font-weight: 600;
            opacity: 0.9;
        }

        .date-badge .days-remaining {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .time-info {
            flex: 1;
        }

        .time-info .time {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .time-info .duration {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .vehicle-info {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-secondary);
        }

        .vehicle-info i {
            color: #667eea;
        }

        /* Indicador de tiempo restante */
        .time-remaining {
            font-size: 0.85rem;
            color: #667eea;
            font-weight: 600;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .status-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        /* ESTILOS DE BADGE PARA PRÓXIMAS CITAS */
        .status-pendiente {
            background: linear-gradient(135deg, #fff3e0, #ffe0b2) !important;
            color: #ef6c00 !important;
            border: 1px solid #ffcc80 !important;
        }

        .status-pendiente:hover {
            background: linear-gradient(135deg, #ffe0b2, #ffcc80) !important;
        }

        .status-confirmada,
        .status-confirmado {
            background: linear-gradient(135deg, #e1f5fe, #b3e5fc) !important;
            color: #0277bd !important;
            border: 1px solid #81d4fa !important;
        }

        .status-confirmada:hover,
        .status-confirmado:hover {
            background: linear-gradient(135deg, #b3e5fc, #81d4fa) !important;
        }

        .status-en_proceso,
        .status-en-proceso {
            background: linear-gradient(135deg, #f1e6ff, #e1bee7) !important;
            color: #6a1b9a !important;
            border: 1px solid #ce93d8 !important;
        }

        .status-en_proceso:hover,
        .status-en-proceso:hover {
            background: linear-gradient(135deg, #e1bee7, #ce93d8) !important;
        }

        .cita-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        /* COLORES DE BORDE PARA SECCIONES SEGÚN ESTADO */
        .cita-card.pendiente .detail-section {
            border-left: 4px solid #4facfe !important;
        }

        .cita-card.confirmada .detail-section,
        .cita-card.confirmado .detail-section {
            border-left: 4px solid #66bb6a !important;
        }

        .cita-card.en_proceso .detail-section,
        .cita-card.en-proceso .detail-section {
            border-left: 4px solid #1b5e20 !important;
        }

        .detail-section {
            background: rgba(255, 255, 255, 0.5);
            padding: 20px;
            border-radius: var(--border-radius);
        }

        .detail-section h4 {
            margin: 0 0 15px 0;
            color: var(--text-primary);
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .service-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .service-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .service-item:last-child {
            border-bottom: none;
        }

        .service-name {
            font-weight: 600;
            flex: 1;
        }

        .service-price {
            color: var(--text-secondary);
            font-weight: 600;
        }

        .cita-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-soft);
        }

        .empty-state i {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: var(--text-primary);
            margin-bottom: 10px;
        }

        .empty-state p {
            color: var(--text-secondary);
            margin-bottom: 20px;
        }

        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
            padding: 12px 16px;
            border-radius: var(--border-radius);
            text-decoration: none;
            transition: var(--transition);
            box-shadow: var(--shadow-soft);
        }

        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .pagination-wrapper {
            margin-top: 30px;
            display: flex;
            justify-content: center;
        }

        /* Estilos mejorados para la paginación */
        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .pagination li {
            display: inline-block;
        }

        .pagination a,
        .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 12px;
            border-radius: var(--border-radius);
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
        }

        .pagination a:hover {
            background: rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
            color: white;
        }

        .pagination .active span {
            background: var(--primary-gradient);
            color: white;
            box-shadow: var(--shadow-soft);
        }

        .pagination .disabled span {
            opacity: 0.6;
            cursor: not-allowed;
            background: rgba(255, 255, 255, 0.3);
        }

        /* Estilos para el modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            margin: 3% auto;
            padding: 30px;
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-hover);
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .close-modal {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: var(--transition);
        }

        .close-modal:hover {
            color: #667eea;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .service-card {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            background: white;
        }

        .service-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: var(--shadow-soft);
        }

        .service-card.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        }

        .service-card input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        .service-card h4 {
            margin: 0 0 5px 0;
            color: var(--text-primary);
        }

        .service-card p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .service-card .description {
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .cita-actions .btn {
            position: relative;
            z-index: 10;
            pointer-events: auto;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .cita-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .cita-details {
                grid-template-columns: 1fr;
            }

            .back-button {
                position: relative;
                top: auto;
                left: auto;
                margin-bottom: 20px;
                display: inline-block;
            }

            .view-nav {
                flex-direction: column;
                gap: 5px;
            }

            .counters-container {
                flex-direction: column;
                gap: 10px;
            }

            .counter-item {
                flex-direction: row;
                justify-content: space-between;
                min-width: auto;
                padding: 10px 0;
                border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            }

            .counter-item:last-child {
                border-bottom: none;
            }

            .fab-button {
                bottom: 20px;
                right: 20px;
                width: 56px;
                height: 56px;
                font-size: 1.3rem;
            }

            .modal-content {
                margin: 5% auto;
                padding: 20px;
                max-width: 95%;
            }

            #serviciosContainer {
                grid-template-columns: 1fr !important;
            }

            /* Paginación responsiva */
            .pagination {
                gap: 4px;
            }

            .pagination a,
            .pagination span {
                min-width: 32px;
                height: 32px;
                padding: 0 8px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
   <a href="{{ route('cliente.dashboard') }}" class="back-button">
        <i class="fas fa-arrow-left"></i> Volver al Dashboard
    </a>

    <!-- Botón flotante para nueva cita -->
    <button class="fab-button" onclick="openCitaModal()" title="Agendar Nueva Cita">
        <i class="fas fa-plus"></i>
    </button>

    <div class="container">
        <div class="header">
            <h1><i class="fas fa-clock"></i> Mis Citas</h1>
            <p>Gestiona tus citas pendientes, confirmadas y en proceso</p>
        </div>

        <!-- Filtros -->
        <div class="filters-section">
            <!-- Contadores específicos para próximas citas -->
            <div class="counters-container">
                <div class="counter-item counter-total">
                    <span class="counter-value" id="total-counter">{{ $citas->total() }}</span>
                    <span class="counter-label">Total Citas</span>
                </div>
                <div class="counter-item counter-pendiente">
                    <span class="counter-value"
                        id="pendiente-counter">{{ $citas->where('estado', 'pendiente')->count() }}</span>
                    <span class="counter-label">Pendientes</span>
                </div>
                <div class="counter-item counter-confirmada">
                    <span class="counter-value"
                        id="confirmada-counter">{{ $citas->where('estado', 'confirmada')->count() }}</span>
                    <span class="counter-label">Confirmadas</span>
                </div>
                <div class="counter-item counter-en_proceso">
                    <span class="counter-value"
                        id="en-proceso-counter">{{ $citas->where('estado', 'en_proceso')->count() }}</span>
                    <span class="counter-label">En Proceso</span>
                </div>
            </div>

            <form id="filtrosForm">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="estado">
                            <i class="fas fa-filter"></i> Estado
                        </label>
                        <select name="estado" id="estado">
                            <option value="">Todos los estados</option>
                            <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>
                                Pendiente</option>
                            <option value="confirmada" {{ request('estado') == 'confirmada' ? 'selected' : '' }}>
                                Confirmada</option>
                            <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>
                                En Proceso</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="fecha_desde">
                            <i class="fas fa-calendar-day"></i> Desde
                        </label>
                        <input type="date" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}">
                    </div>

                    <div class="filter-group">
                        <label for="fecha_hasta">
                            <i class="fas fa-calendar-day"></i> Hasta
                        </label>
                        <input type="date" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}">
                    </div>

                    <div class="filter-group">
                        <label for="vehiculo_id">
                            <i class="fas fa-car"></i> Vehículo
                        </label>
                        <select name="vehiculo_id" id="vehiculo_id">
                            <option value="">Todos los vehículos</option>
                            @foreach (auth()->user()->vehiculos as $vehiculo)
                                <option value="{{ $vehiculo->id }}"
                                    {{ request('vehiculo_id') == $vehiculo->id ? 'selected' : '' }}>
                                    {{ $vehiculo->marca }} {{ $vehiculo->modelo }}
                                    @if ($vehiculo->placa)
                                        - {{ $vehiculo->placa }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <button type="button" class="btn btn-primary" onclick="limpiarFiltros()">
                            <i class="fas fa-times"></i> Limpiar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Lista de citas -->
        <div id="citas-container">
            @if ($citas->count() > 0)
                <div class="citas-grid">
                    @foreach ($citas as $cita)
                        @php
                            $isFuture = $cita->fecha_hora > now();
                            // Cambio aquí: usar floor() para redondear hacia abajo y mostrar solo números enteros
                            $daysDiff = $isFuture ? floor(now()->diffInDays($cita->fecha_hora)) : null;
                            $hoursRemaining = $isFuture ? floor(now()->diffInHours($cita->fecha_hora)) : null;

                            $cardClass = $cita->estado;
                            if ($isFuture && $daysDiff <= 1 && $cita->estado == 'confirmada') {
                                $cardClass .= ' urgent';
                            } elseif ($isFuture && $daysDiff <= 3 && $cita->estado == 'confirmada') {
                                $cardClass .= ' coming-soon';
                            }

                            $timeRemaining = '';
                            if ($isFuture) {
                                if ($daysDiff == 0) {
                                    $timeRemaining = $hoursRemaining . ' hora' . ($hoursRemaining != 1 ? 's' : '');
                                } else {
                                    $timeRemaining = $daysDiff . ' día' . ($daysDiff != 1 ? 's' : '');
                                }
                            }
                        @endphp

                        <div class="cita-card {{ $cardClass }}" data-cita-id="{{ $cita->id }}">
                            <div class="cita-header">
                                <div class="cita-date-time">
                                    <div class="date-badge {{ $cita->estado }}">
                                        <span class="day">{{ $cita->fecha_hora->format('d') }}</span>
                                        <span class="month">{{ $cita->fecha_hora->format('M') }}</span>
                                        @if ($isFuture && $daysDiff <= 1 && $cita->estado == 'confirmada')
                                            <span class="days-remaining">!</span>
                                        @endif
                                    </div>
                                    <div class="time-info">
                                        <div class="time">{{ $cita->fecha_hora->format('h:i A') }}</div>
                                        <div class="duration">
                                            <i class="fas fa-clock"></i>
                                            Duración: {{ $cita->servicios->sum('duracion_min') }} min
                                        </div>
                                        @if ($timeRemaining)
                                            <div class="time-remaining">
                                                <i class="fas fa-hourglass-half"></i>
                                                En {{ $timeRemaining }}
                                            </div>
                                        @endif
                                        <div class="vehicle-info">
                                            <i class="fas fa-car"></i>
                                            {{ $cita->vehiculo->marca }} {{ $cita->vehiculo->modelo }}
                                            @if ($cita->vehiculo->placa)
                                                - {{ $cita->vehiculo->placa }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <span class="status-badge status-{{ str_replace('_', '-', $cita->estado) }}">
                                    {{ ucfirst(str_replace('_', ' ', $cita->estado)) }}
                                </span>
                            </div>

                            <div class="cita-details">
                                <div class="detail-section">
                                    <h4><i class="fas fa-tools"></i> Servicios Programados</h4>
                                    <ul class="service-list">
                                        @foreach ($cita->servicios as $servicio)
                                            <li class="service-item">
                                                <span class="service-name">{{ $servicio->nombre }}</span>
                                                <span
                                                    class="service-price">${{ number_format($servicio->precio, 2) }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <div style="border-top: 2px solid #667eea; margin-top: 10px; padding-top: 10px;">
                                        <strong>Total a Pagar:
                                            ${{ number_format($cita->servicios->sum('precio'), 2) }}</strong>
                                    </div>
                                </div>

                                @if ($cita->observaciones)
                                    <div class="detail-section">
                                        <h4><i class="fas fa-comment"></i> Observaciones</h4>
                                        <p>{{ $cita->observaciones }}</p>
                                    </div>
                                @endif

                                <!-- Información adicional según el estado -->
                                @if ($cita->estado == 'pendiente')
                                    <div class="detail-section">
                                        <h4><i class="fas fa-info-circle"></i> Estado de la Cita</h4>
                                        <p>Tu cita está <strong>pendiente de confirmación</strong>. Recibirás una
                                            notificación cuando sea confirmada.</p>
                                    </div>
                                @elseif ($cita->estado == 'confirmada')
                                    <div class="detail-section">
                                        <h4><i class="fas fa-check-circle"></i> Cita Confirmada</h4>
                                        <p>Tu cita ha sido <strong>confirmada</strong>. Por favor llega 10 minutos antes
                                            de la hora programada.</p>
                                        @if ($daysDiff <= 1)
                                            <p style="color: #dc3545; font-weight: 600;">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                ¡Cita próxima! Recuerda asistir puntualmente.
                                            </p>
                                        @endif
                                    </div>
                                @elseif ($cita->estado == 'en_proceso')
                                    <div class="detail-section">
                                        <h4><i class="fas fa-cog"></i> En Proceso</h4>
                                        <p>Tu vehículo está siendo atendido. Te notificaremos cuando esté listo.</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Acciones disponibles para citas pendientes y confirmadas -->
                            @if (in_array($cita->estado, ['pendiente', 'confirmada']))
                                <div class="cita-actions">
                                    <button class="btn btn-sm btn-warning" onclick="editCita({{ $cita->id }})">
                                        <i class="fas fa-edit"></i> Modificar
                                    </button>
                                    <button class="btn btn-sm btn-outline" onclick="editCita({{ $cita->id }})">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Paginación mejorada -->
                <div class="pagination-wrapper">
                    @if ($citas->hasPages())
                        <ul class="pagination">
                            {{-- Previous Page Link --}}
                            @if ($citas->onFirstPage())
                                <li class="disabled" aria-disabled="true">
                                    <span><i class="fas fa-chevron-left"></i></span>
                                </li>
                            @else
                                <li>
                                    <a href="{{ $citas->previousPageUrl() }}" rel="prev">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            @endif

                            {{-- Pagination Elements --}}
                            @foreach ($citas->getUrlRange(1, $citas->lastPage()) as $page => $url)
                                @if ($page == $citas->currentPage())
                                    <li class="active" aria-current="page">
                                        <span>{{ $page }}</span>
                                    </li>
                                @else
                                    <li>
                                        <a href="{{ $url }}">{{ $page }}</a>
                                    </li>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($citas->hasMorePages())
                                <li>
                                    <a href="{{ $citas->nextPageUrl() }}" rel="next">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            @else
                                <li class="disabled" aria-disabled="true">
                                    <span><i class="fas fa-chevron-right"></i></span>
                                </li>
                            @endif
                        </ul>
                    @endif
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-calendar-plus"></i>
                    <h3>No tienes citas programadas</h3>
                    <p>¿Necesitas agendar un servicio para tu vehículo?</p>
                    <button class="btn btn-primary" onclick="openCitaModal()">
                        <i class="fas fa-calendar-plus"></i> Agendar Nueva Cita
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal para crear/editar cita -->
    <div id="createCitaModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeCitaModal()">&times;</span>
            <h2 style="color: #4facfe; margin-bottom: 20px;">
                <i class="fas fa-calendar-plus"></i> <span id="modalTitle">Nueva Cita</span>
            </h2>

            <form id="citaForm" method="POST" action="{{ route('cliente.citas.store') }}"
                enctype="multipart/form-data">
                @csrf
                <!-- Campo oculto para ID de cita (solo en edición) -->
                <input type="hidden" id="form_cita_id" name="cita_id" value="">

                <!-- Selección de vehículo -->
                <div class="form-group">
                    <label for="modal_vehiculo_id">Vehículo: <span style="color: red;">*</span></label>
                    <select id="modal_vehiculo_id" name="vehiculo_id" required onchange="cargarServiciosPorTipo()">
                        <option value="">Seleccione un vehículo</option>
                        @foreach (auth()->user()->vehiculos as $vehiculo)
                            <option value="{{ $vehiculo->id }}" data-tipo="{{ $vehiculo->tipo }}">
                                {{ $vehiculo->marca }} {{ $vehiculo->modelo }} - {{ $vehiculo->placa }}
                                ({{ ucfirst($vehiculo->tipo) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Fecha -->
                <div class="form-group">
                    <label for="fecha">Fecha: <span style="color: red;">*</span></label>
                    <input type="date" id="fecha" name="fecha" required min="{{ date('Y-m-d') }}"
                        max="{{ date('Y-m-d', strtotime('+1 month')) }}">
                </div>

                <!-- Hora -->
                <div class="form-group">
                    <label for="hora">Hora: <span style="color: red;">*</span></label>
                    <select id="hora" name="hora" required>
                        <option value="">Seleccione una hora</option>
                    </select>
                </div>

                <!-- Servicios -->
                <div class="form-group">
                    <label>Servicios Disponibles: <span style="color: red;">*</span></label>
                    <div id="serviciosContainer"
                        style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin-top: 10px; min-height: 100px;">
                        <p>Seleccione un vehículo primero</p>
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="form-group">
                    <label for="observaciones">Observaciones:</label>
                    <textarea id="observaciones" name="observaciones" rows="3" maxlength="500"
                        placeholder="Información adicional sobre su vehículo o servicio requerido..."></textarea>
                </div>

                <!-- Botones -->
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn btn-outline" onclick="closeCitaModal()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitButton">
                        <i class="fas fa-save"></i> <span id="submitText">Guardar Cita</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Configuración global de SweetAlert
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-outline mr-2'
            },
            buttonsStyling: false
        });

        // Variables globales
        let horariosDisponibles = [];
        let todosServiciosDisponibles = [];
        let serviciosFiltrados = [];
        let diasNoLaborables = [];

        // Función para abrir modal de cita
        async function openCitaModal(vehiculoId = null) {
            return new Promise(async (resolve, reject) => {
                try {
                    const isActive = await checkUserStatus();
                    if (!isActive) {
                        swalWithBootstrapButtons.fire({
                            title: 'Cuenta inactiva',
                            text: 'Tu cuenta está inactiva. No puedes crear nuevas citas.',
                            icon: 'error'
                        });
                        return reject('Cuenta inactiva');
                    }

                    const modal = document.getElementById('createCitaModal');
                    if (!modal) {
                        return reject('Modal de cita no encontrado');
                    }

                    // Resetear completamente el formulario
                    const citaForm = document.getElementById('citaForm');
                    if (citaForm) {
                        citaForm.reset();
                        citaForm.action = '{{ route('cliente.citas.store') }}';

                        // Eliminar cualquier campo _method
                        const methodInput = citaForm.querySelector('[name="_method"]');
                        if (methodInput) methodInput.remove();

                        // Limpiar ID de cita
                        const citaIdInput = document.getElementById('form_cita_id');
                        if (citaIdInput) citaIdInput.value = '';

                        // Restablecer el título y texto del botón
                        setModalMode(false); // Modo creación

                        // Limpiar servicios seleccionados
                        document.querySelectorAll('.service-card.selected').forEach(card => {
                            card.classList.remove('selected');
                        });

                        // Resetear select de hora
                        const horaSelect = document.getElementById('hora');
                        if (horaSelect) {
                            horaSelect.innerHTML = '<option value="">Seleccione una hora</option>';
                        }
                    }

                    // Cargar datos iniciales
                    const loading = swalWithBootstrapButtons.fire({
                        title: 'Preparando formulario...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    try {
                        await loadInitialData();
                        loading.close();

                        // Mostrar modal
                        modal.style.display = 'block';
                        await new Promise(resolve => setTimeout(resolve, 100));

                        // Establecer vehículo si se proporciona
                        if (vehiculoId) {
                            const vehiculoSelect = document.getElementById('modal_vehiculo_id');
                            if (vehiculoSelect) {
                                vehiculoSelect.value = vehiculoId;
                                await cargarServiciosPorTipo();
                            }
                        }

                        console.log('Modal abierto para CREAR nueva cita');
                        resolve();
                    } catch (error) {
                        loading.close();
                        reject(error);
                    }
                } catch (error) {
                    reject(error);
                }
            });
        }

        function closeCitaModal() {
            document.getElementById('createCitaModal').style.display = 'none';
            document.getElementById('citaForm').reset();
        }

        function setModalMode(isEdit = false) {
            const modalTitle = document.getElementById('modalTitle');
            const submitText = document.getElementById('submitText');

            if (modalTitle) {
                modalTitle.textContent = isEdit ? 'Editar Cita' : 'Nueva Cita';
            }

            if (submitText) {
                submitText.textContent = isEdit ? 'Actualizar Cita' : 'Guardar Cita';
            }
        }

        async function checkUserStatus() {
            try {
                const response = await fetch('{{ route('cliente.check-status') }}', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();
                return data.is_active;
            } catch (error) {
                console.error('Error al verificar estado:', error);
                return false;
            }
        }

        // Función para cargar datos iniciales
        async function loadInitialData() {
            try {
                console.log('Iniciando carga de datos...');

                // Cargar datos en paralelo
                const [horariosRes, serviciosRes, noLaborablesRes] = await Promise.all([
                    fetch('{{ route('cliente.horarios-disponibles') }}').catch(e => {
                        console.error('Error cargando horarios:', e);
                        return {
                            ok: false
                        };
                    }),
                    fetch('{{ route('cliente.servicios-disponibles') }}').catch(e => {
                        console.error('Error cargando servicios:', e);
                        return {
                            ok: false
                        };
                    }),
                    fetch('{{ route('cliente.dias-no-laborables') }}').catch(e => {
                        console.error('Error cargando días no laborables:', e);
                        return {
                            ok: false
                        };
                    })
                ]);

                // Verificar respuestas y procesar
                if (horariosRes.ok) {
                    horariosDisponibles = await horariosRes.json();
                    console.log('Horarios cargados:', horariosDisponibles.length);
                } else {
                    horariosDisponibles = [];
                    console.error('Error cargando horarios disponibles');
                }

                if (serviciosRes.ok) {
                    todosServiciosDisponibles = await serviciosRes.json();
                    console.log('Servicios cargados:', Object.keys(todosServiciosDisponibles));
                } else {
                    todosServiciosDisponibles = {};
                    console.error('Error cargando servicios disponibles');
                }

                if (noLaborablesRes.ok) {
                    diasNoLaborables = await noLaborablesRes.json();
                    console.log('Días no laborables cargados:', diasNoLaborables.length);
                } else {
                    diasNoLaborables = [];
                    console.error('Error cargando días no laborables');
                }

                // Configurar datepicker
                setupDatePicker();

                console.log('Datos iniciales cargados completamente');
                return true;

            } catch (error) {
                console.error('Error crítico cargando datos iniciales:', error);

                // Configurar valores por defecto
                horariosDisponibles = horariosDisponibles || [];
                todosServiciosDisponibles = todosServiciosDisponibles || {};
                diasNoLaborables = diasNoLaborables || [];

                swalWithBootstrapButtons.fire({
                    title: 'Error de conexión',
                    text: 'Hubo problemas cargando algunos datos. Algunas funciones pueden estar limitadas.',
                    icon: 'warning'
                });

                return false;
            }
        }

        // Función para cargar horas disponibles 
        async function loadAvailableHours(selectedDate, excludeCitaId = null) {
            const horaSelect = document.getElementById('hora');

            console.log('Cargando horarios para fecha:', selectedDate, '| Excluir cita:', excludeCitaId);

            horaSelect.innerHTML = '<option value="">Cargando horarios...</option>';

            try {
                // Usar la fecha local correctamente
                const fechaLocal = createLocalDate(selectedDate);
                const dayOfWeekJS = fechaLocal.getDay(); // 0=Domingo, 1=Lunes, etc.
                const dayOfWeekBackend = getBackendDayFromJSDay(dayOfWeekJS);

                console.log('Fecha seleccionada:', selectedDate);
                console.log('Día JS:', dayOfWeekJS, 'Día Backend:', dayOfWeekBackend);

                // Validar si es domingo
                if (dayOfWeekJS === 0) {
                    horaSelect.innerHTML = '<option value="">No hay horarios (No atendemos domingos)</option>';
                    return;
                }

                // Verificar día no laborable
                const diaNoLaborable = diasNoLaborables.find(dia => dia.fecha === selectedDate);
                if (diaNoLaborable) {
                    horaSelect.innerHTML = `<option value="">${diaNoLaborable.motivo || 'Día no laborable'}</option>`;
                    return;
                }

                // Obtener horarios ocupados
                let citasExistentes = [];
                try {
                    const url =
                        `/cliente/citas/horarios-ocupados?fecha=${selectedDate}${excludeCitaId ? `&exclude=${excludeCitaId}` : ''}`;
                    console.log('Consultando horarios ocupados:', url);

                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) throw new Error(`Error ${response.status}`);

                    const data = await response.json();
                    citasExistentes = data.horariosOcupados || [];

                    console.log('Horarios ocupados recibidos:', citasExistentes);
                } catch (error) {
                    console.error('Error al obtener horarios ocupados:', error);
                    // Continuar sin horarios ocupados
                }

                // Generar opciones de horario
                horaSelect.innerHTML = '<option value="">Seleccione una hora</option>';

                const horariosDia = horariosDisponibles.filter(h => h.dia_semana == dayOfWeekBackend);

                console.log('Horarios disponibles para día', dayOfWeekBackend, ':', horariosDia);

                if (horariosDia.length === 0) {
                    horaSelect.innerHTML = '<option value="">No hay horarios programados</option>';
                    return;
                }

                let horariosGenerados = 0;

                horariosDia.forEach(horario => {
                    const [inicioH, inicioM] = horario.hora_inicio.split(':').map(Number);
                    const [finH, finM] = horario.hora_fin.split(':').map(Number);

                    let horaActual = new Date();
                    horaActual.setHours(inicioH, inicioM, 0, 0);

                    const horaFin = new Date();
                    horaFin.setHours(finH, finM, 0, 0);

                    while (horaActual < horaFin) {
                        const horaStr = horaActual.getHours().toString().padStart(2, '0') + ':' +
                            horaActual.getMinutes().toString().padStart(2, '0');

                        // Verificar colisión con citas existentes
                        const estaOcupado = citasExistentes.some(cita => {
                            try {
                                const inicioCita = new Date(`${selectedDate}T${cita.hora_inicio}`);
                                const finCita = new Date(inicioCita.getTime() + (cita.duracion || 30) *
                                    60000);

                                const inicioPropuesta = new Date(`${selectedDate}T${horaStr}`);
                                const finPropuesta = new Date(inicioPropuesta.getTime() +
                                    calcularDuracionServiciosSeleccionados() * 60000);

                                return (
                                    (inicioPropuesta >= inicioCita && inicioPropuesta < finCita) ||
                                    (finPropuesta > inicioCita && finPropuesta <= finCita) ||
                                    (inicioPropuesta <= inicioCita && finPropuesta >= finCita)
                                );
                            } catch (e) {
                                console.error('Error al verificar colisión:', e);
                                return false;
                            }
                        });

                        const option = document.createElement('option');
                        option.value = horaStr;
                        option.textContent = horaStr;

                        if (estaOcupado) {
                            option.disabled = true;
                            option.textContent += ' (Ocupado)';
                            option.style.color = '#ff6b6b';
                        } else {
                            horariosGenerados++;
                        }

                        horaSelect.appendChild(option);
                        horaActual.setMinutes(horaActual.getMinutes() + 30);
                    }
                });

                if (horariosGenerados === 0 && horaSelect.options.length > 1) {
                    horaSelect.innerHTML = '<option value="">No hay horarios disponibles</option>';
                }

                console.log(`Horarios cargados - Generados: ${horariosGenerados}`);

            } catch (error) {
                console.error('Error en loadAvailableHours:', error);
                horaSelect.innerHTML = '<option value="">Error al cargar horarios</option>';
            }
        }

        // Configuración del datepicker
        function setupDatePicker() {
            const fechaInput = document.getElementById('fecha');

            // Establecer fechas mínima y máxima correctamente
            const hoy = new Date();
            const unMesAdelante = new Date();
            unMesAdelante.setMonth(unMesAdelante.getMonth() + 1);

            fechaInput.min = getLocalDateString(hoy);
            fechaInput.max = getLocalDateString(unMesAdelante);

            console.log('Datepicker configurado:', {
                min: fechaInput.min,
                max: fechaInput.max,
                today: getLocalDateString(hoy)
            });

            fechaInput.addEventListener('change', function() {
                console.log('📅 Fecha cambiada:', this.value);

                if (!this.value) {
                    document.getElementById('hora').innerHTML = '<option value="">Seleccione una hora</option>';
                    return;
                }

                try {
                    const selectedDate = createLocalDate(this.value);
                    const dayOfWeekJS = selectedDate.getDay();

                    console.log('Fecha parseada:', selectedDate);
                    console.log('Día de la semana JS:', dayOfWeekJS);

                    // Validar domingos primero
                    if (dayOfWeekJS === 0) {
                        showDateError('Domingo no laborable',
                            'No trabajamos los domingos. Por favor selecciona otro día.');
                        this.value = '';
                        document.getElementById('hora').innerHTML = '<option value="">Seleccione una hora</option>';
                        return;
                    }

                    // Verificar días no laborables
                    const diaNoLaborable = diasNoLaborables.find(dia => dia.fecha === this.value);
                    if (diaNoLaborable) {
                        showDateError(
                            'Día no laborable',
                            `No se atienden citas el ${formatFechaBonita(selectedDate)}.<br>
                             <strong>Motivo:</strong> ${diaNoLaborable.motivo || 'Día no laborable'}`
                        );
                        this.value = '';
                        return;
                    }

                    // ÚNICO LUGAR donde se cargan horarios - al cambiar fecha
                    const citaId = document.getElementById('form_cita_id')?.value;
                    loadAvailableHours(this.value, citaId);

                } catch (error) {
                    console.error('Error al procesar fecha:', error);
                    showDateError('Error', 'Fecha inválida. Por favor selecciona una fecha válida.');
                    this.value = '';
                }
            });
        }

        function calcularDuracionServiciosSeleccionados() {
            let total = 0;
            document.querySelectorAll('input[name="servicios[]"]:checked').forEach(checkbox => {
                const servicioId = checkbox.value;
                // Buscar el servicio en todos los servicios disponibles
                for (const categoria in todosServiciosDisponibles) {
                    const servicio = todosServiciosDisponibles[categoria].find(s => s.id == servicioId);
                    if (servicio) {
                        total += servicio.duracion_min;
                        break;
                    }
                }
            });
            return total || 30; // Default 30 mins si no hay selección
        }

        // Función para formatear fecha bonita (ej: "Lunes, 25 de Junio")
        function formatFechaBonita(date) {
            const options = {
                weekday: 'long',
                day: 'numeric',
                month: 'long'
            };
            return date.toLocaleDateString('es-ES', options);
        }

        function showDateError(title, message) {
            swalWithBootstrapButtons.fire({
                title: title,
                html: message,
                icon: 'warning',
                confirmButtonColor: '#4facfe'
            });

            // Resetear selección
            document.getElementById('fecha').value = '';
            document.getElementById('hora').innerHTML = '<option value="">Seleccione una hora</option>';
        }

        // Función para cargar servicios según el tipo de vehículo seleccionado
        async function cargarServiciosPorTipo() {
            return new Promise(async (resolve, reject) => {
                try {
                    const vehiculoSelect = document.getElementById('modal_vehiculo_id');
                    const serviciosContainer = document.getElementById('serviciosContainer');

                    if (!vehiculoSelect) {
                        console.error('Select de vehículo no encontrado');
                        return reject('Select de vehículo no encontrado');
                    }

                    if (!serviciosContainer) {
                        console.error('Container de servicios no encontrado');
                        return reject('Container de servicios no encontrado');
                    }

                    const selectedOption = vehiculoSelect.options[vehiculoSelect.selectedIndex];
                    const tipoVehiculo = selectedOption?.dataset.tipo?.toLowerCase();

                    if (!tipoVehiculo) {
                        serviciosContainer.innerHTML = '<p>Seleccione un vehículo primero</p>';
                        return resolve();
                    }

                    // Mostrar loading
                    serviciosContainer.innerHTML = '<p>Cargando servicios...</p>';

                    // Si no tenemos los servicios disponibles, cargarlos
                    if (!todosServiciosDisponibles || Object.keys(todosServiciosDisponibles).length === 0) {
                        console.log('Cargando servicios desde servidor...');
                        await loadInitialData();
                    }

                    // Filtrar servicios por tipo
                    const serviciosFiltrados = [];
                    for (const categoria in todosServiciosDisponibles) {
                        if (categoria.toLowerCase() === tipoVehiculo) {
                            serviciosFiltrados.push(...todosServiciosDisponibles[categoria]);
                        }
                    }

                    console.log('Servicios filtrados para', tipoVehiculo, ':', serviciosFiltrados);

                    if (serviciosFiltrados.length === 0) {
                        console.error('No se encontraron servicios para:', tipoVehiculo);
                        console.log('Todos los servicios disponibles:', todosServiciosDisponibles);
                        serviciosContainer.innerHTML =
                            '<p>No hay servicios disponibles para este tipo de vehículo</p>';
                        return resolve();
                    }

                    await renderServicios(serviciosFiltrados);
                    resolve();

                } catch (error) {
                    console.error('Error en cargarServiciosPorTipo:', error);
                    const serviciosContainer = document.getElementById('serviciosContainer');
                    if (serviciosContainer) {
                        serviciosContainer.innerHTML = '<p>Error al cargar servicios</p>';
                    }
                    reject(error);
                }
            });
        }

        // Función renderServicios 
        function renderServicios(servicios) {
            return new Promise((resolve, reject) => {
                try {
                    const container = document.getElementById('serviciosContainer');

                    if (!container) {
                        return reject('Container de servicios no encontrado');
                    }

                    container.innerHTML = '';

                    if (servicios.length === 0) {
                        container.innerHTML = '<p>No hay servicios disponibles para este tipo de vehículo</p>';
                        return resolve();
                    }

                    servicios.forEach(servicio => {
                        const servicioDiv = document.createElement('label');
                        servicioDiv.className = 'service-card';
                        servicioDiv.htmlFor = `servicio_${servicio.id}`;
                        servicioDiv.innerHTML = `
                            <input type="checkbox" id="servicio_${servicio.id}" name="servicios[]" value="${servicio.id}">
                            <div>
                                <h4>${servicio.nombre}</h4>
                                <p>${servicio.precio.toFixed(2)} • ${formatDuration(servicio.duracion_min)}</p>
                                <p class="description">${servicio.descripcion || ''}</p>
                            </div>
                        `;
                        container.appendChild(servicioDiv);

                        const checkbox = servicioDiv.querySelector('input');
                        if (checkbox) {
                            // SOLO cambiar la apariencia visual, NO recargar horarios
                            checkbox.addEventListener('change', function() {
                                servicioDiv.classList.toggle('selected', this.checked);

                                // Log para debug (opcional)
                                const duracionTotal = calcularDuracionServiciosSeleccionados();
                                console.log(
                                    `Servicio ${this.checked ? 'seleccionado' : 'deseleccionado'}: ${servicio.nombre}`
                                );
                                console.log(`Duración total actualizada: ${duracionTotal} minutos`);

                                // Opcional: Validar que la duración no exceda el horario laboral
                                const horaSelect = document.getElementById('hora');
                                if (horaSelect && horaSelect.value && this.checked) {
                                    validateServiceDuration(horaSelect.value, duracionTotal);
                                }
                            });
                        }
                    });
                    console.log('Servicios renderizados exitosamente SIN recargar horarios:', servicios.length);
                    setTimeout(() => resolve(), 50);

                } catch (error) {
                    console.error('Error en renderServicios:', error);
                    reject(error);
                }
            });
        }

        // Función auxiliar para validar duración de servicios (opcional)
        function validateServiceDuration(horaSeleccionada, duracionTotal) {
            try {
                const [horas, minutos] = horaSeleccionada.split(':').map(Number);
                const horaInicio = new Date();
                horaInicio.setHours(horas, minutos, 0, 0);
                const horaFin = new Date(horaInicio.getTime() + duracionTotal * 60000);

                // Verificar si excede las 6:00 PM (18:00)
                if (horaFin.getHours() > 18 || (horaFin.getHours() === 18 && horaFin.getMinutes() > 0)) {
                    console.warn('⚠️ Los servicios seleccionados podrían exceder el horario laboral (6:00 PM)');

                    // Mostrar advertencia visual sutil (opcional)
                    const horaSelect = document.getElementById('hora');
                    if (horaSelect) {
                        horaSelect.style.borderColor = '#ffa500';
                        horaSelect.title = 'Los servicios seleccionados podrían exceder el horario laboral';

                        // Remover advertencia después de 3 segundos
                        setTimeout(() => {
                            horaSelect.style.borderColor = '';
                            horaSelect.title = '';
                        }, 3000);
                    }
                }
            } catch (error) {
                console.error('Error al validar duración:', error);
            }
        }

        // Función para formatear duración
        function formatDuration(minutes) {
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;

            if (hours > 0) {
                return mins > 0 ? `${hours}h ${mins}min` : `${hours}h`;
            }
            return `${mins}min`;
        }

        async function setSelectedHourForEdit(hora24, maxAttempts = 10, interval = 300) {
            let attempts = 0;

            return new Promise((resolve) => {
                const checkInterval = setInterval(() => {
                    const horaSelect = document.getElementById('hora');
                    attempts++;

                    // Si encontramos la hora y está disponible
                    const targetOption = Array.from(horaSelect.options).find(
                        opt => opt.value === hora24 && !opt.disabled
                    );

                    if (targetOption) {
                        horaSelect.value = hora24;
                        console.log('Hora establecida exitosamente:', hora24);
                        clearInterval(checkInterval);
                        resolve(true);
                    }
                    // Si se agotaron los intentos
                    else if (attempts >= maxAttempts) {
                        console.warn('No se pudo establecer la hora después de intentos');
                        clearInterval(checkInterval);

                        // Forzar la creación de la opción si no existe
                        const newOption = document.createElement('option');
                        newOption.value = hora24;
                        newOption.textContent = `${hora24} (Actual)`;
                        horaSelect.appendChild(newOption);
                        horaSelect.value = hora24;

                        resolve(false);
                    }
                }, interval);
            });
        }

        // Función para cancelar citas
        function cancelCita(citaId) {
            swalWithBootstrapButtons.fire({
                title: '¿Cancelar cita?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No, mantener'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/cliente/citas/${citaId}/cancelar`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin'
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(err => {
                                    throw err;
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                swalWithBootstrapButtons.fire({
                                    title: 'Cancelada',
                                    text: data.message,
                                    icon: 'success'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                throw new Error(data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            let errorMsg = typeof error === 'string' ? error : (error.message ||
                                'Error al cancelar la cita');

                            swalWithBootstrapButtons.fire({
                                title: 'Error',
                                text: errorMsg,
                                icon: 'error'
                            });
                        });
                }
            });
        }

        // Función para editar citas
        async function editCita(citaId) {
            console.log('Editando cita ID:', citaId);

            const swalInstance = swalWithBootstrapButtons.fire({
                title: 'Cargando cita...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                // 1. Obtener datos de la cita
                const response = await fetch(`/cliente/citas/${citaId}/edit`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || `Error ${response.status}`);
                }

                const data = await response.json();
                if (!data.success) throw new Error(data.message);

                swalInstance.close();

                // 2. Abrir modal limpio
                await openCitaModal();
                setModalMode(true); // Modo edición

                await new Promise(resolve => setTimeout(resolve, 300));

                // 3. Configurar formulario
                const form = document.getElementById('citaForm');
                const vehiculoSelect = document.getElementById('modal_vehiculo_id');
                const fechaInput = document.getElementById('fecha');
                const formCitaId = document.getElementById('form_cita_id');
                const observacionesInput = document.getElementById('observaciones');

                if (!form) throw new Error('Formulario no encontrado');

                // Configurar para edición
                form.action = `/cliente/citas/${citaId}`;
                let methodInput = form.querySelector('[name="_method"]');
                if (!methodInput) {
                    methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    form.appendChild(methodInput);
                }
                methodInput.value = 'PUT';

                if (formCitaId) formCitaId.value = citaId;

                // 4. Rellenar datos básicos
                if (vehiculoSelect && data.data.vehiculo_id) {
                    vehiculoSelect.value = data.data.vehiculo_id;
                }

                if (observacionesInput) {
                    observacionesInput.value = data.data.observaciones || '';
                }

                // 5. Cargar servicios por tipo de vehículo
                if (data.data.vehiculo_id) {
                    await cargarServiciosPorTipo();
                    await new Promise(resolve => setTimeout(resolve, 200));
                }

                // 6. Establecer fecha (esto disparará la carga de horarios)
                if (fechaInput && data.data.fecha) {
                    fechaInput.value = data.data.fecha;
                    const changeEvent = new Event('change');
                    fechaInput.dispatchEvent(changeEvent);

                    // Esperar explícitamente a que termine loadAvailableHours
                    await new Promise(resolve => setTimeout(resolve, 800));
                }

                // 7. Configurar hora (DESPUÉS de que se carguen los horarios)
                if (data.data.hora) {
                    await setSelectedHourForEdit(data.data.hora);
                }

                // 8. Seleccionar servicios
                if (data.data.cita && data.data.cita.servicios) {
                    data.data.cita.servicios.forEach(servicio => {
                        const checkbox = document.querySelector(
                            `input[name="servicios[]"][value="${servicio.id}"]`);
                        if (checkbox) {
                            checkbox.checked = true;
                            const serviceCard = checkbox.closest('.service-card');
                            if (serviceCard) {
                                serviceCard.classList.add('selected');
                            }
                        }
                    });
                }

                console.log('✅ Cita cargada para edición exitosamente');

            } catch (error) {
                swalInstance.close();
                console.error('Error en editCita:', error);
                swalWithBootstrapButtons.fire({
                    title: 'Error',
                    text: error.message || 'Ocurrió un error al cargar la cita',
                    icon: 'error'
                });
            }
        }

        // FUNCIÓN para convertir día de JavaScript a formato backend
        function getBackendDayFromJSDay(jsDay) {
            // JavaScript: 0=Domingo, 1=Lunes, 2=Martes, 3=Miércoles, 4=Jueves, 5=Viernes, 6=Sábado
            // Backend: 1=Lunes, 2=Martes, 3=Miércoles, 4=Jueves, 5=Viernes, 6=Sábado, 7=Domingo

            if (jsDay === 0) {
                return 7; // Domingo
            }
            return jsDay; // Lunes=1, Martes=2, etc.
        }

        // FUNCIÓN para obtener fecha en timezone local
        function getLocalDateString(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // FUNCIÓN para crear fecha desde string sin problemas de timezone
        function createLocalDate(dateString) {
            const [year, month, day] = dateString.split('-').map(Number);
            return new Date(year, month - 1, day); // month - 1 porque los meses en JS van de 0-11
        }

        // Manejar envío del formulario de citas
        document.addEventListener('DOMContentLoaded', function() {
            const citaForm = document.getElementById('citaForm');

            if (citaForm) {
                citaForm.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    // Validar que al menos un servicio esté seleccionado
                    const serviciosSeleccionados = document.querySelectorAll(
                        'input[name="servicios[]"]:checked');
                    if (serviciosSeleccionados.length === 0) {
                        swalWithBootstrapButtons.fire('Error', 'Debes seleccionar al menos un servicio',
                            'error');
                        return;
                    }

                    // Validar fecha y hora no sean en el pasado
                    const fechaInput = document.getElementById('fecha');
                    const horaInput = document.getElementById('hora');
                    const fechaHoraCita = new Date(`${fechaInput.value}T${horaInput.value}`);
                    const ahora = new Date();

                    if (fechaHoraCita < ahora) {
                        swalWithBootstrapButtons.fire({
                            title: 'Error',
                            text: 'No puedes agendar citas en fechas u horas pasadas',
                            icon: 'error'
                        });
                        return;
                    }

                    const isEdit = document.getElementById('form_cita_id').value;

                    // Mostrar loader
                    const swalInstance = swalWithBootstrapButtons.fire({
                        title: isEdit ? 'Actualizando cita...' : 'Procesando cita...',
                        html: isEdit ? 'Estamos actualizando tu cita, por favor espera' :
                            'Estamos reservando tu cita, por favor espera',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    const form = this;
                    const formData = new FormData(form);

                    // Agregar el ID de la cita si es edición
                    if (isEdit) {
                        formData.append('cita_id', isEdit);
                    }

                    // Configurar método HTTP correcto
                    const method = isEdit ? 'PUT' : 'POST';

                    // Para PUT necesitamos agregar _method
                    if (method === 'PUT') {
                        formData.append('_method', 'PUT');
                    }

                    console.log('Enviando formulario:', {
                        url: form.action,
                        method: method,
                        isEdit: isEdit,
                        citaId: isEdit
                    });

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST', // Siempre POST, Laravel maneja _method
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            },
                            body: formData
                        });

                        const result = await response.json();
                        await swalInstance.close();

                        if (!response.ok) {
                            throw new Error(result.message || 'Error al procesar la cita');
                        }

                        // Éxito
                        closeCitaModal();

                        await swalWithBootstrapButtons.fire({
                            title: isEdit ? '¡Cita actualizada!' : '¡Cita agendada!',
                            html: `
                                <div style="text-align: left; margin-top: 15px;">
                                    <p><strong>Fecha:</strong> ${new Date(result.data.fecha_hora).toLocaleDateString('es-ES', { 
                                        weekday: 'long', 
                                        day: 'numeric', 
                                        month: 'long', 
                                        year: 'numeric' 
                                    })}</p>
                                    <p><strong>Hora:</strong> ${new Date(result.data.fecha_hora).toLocaleTimeString('es-ES', { 
                                        hour: '2-digit', 
                                        minute: '2-digit' 
                                    })}</p>
                                    <p><strong>Servicios:</strong> ${result.data.servicios_nombres}</p>
                                    <p><strong>Vehículo:</strong> ${result.data.vehiculo_marca} ${result.data.vehiculo_modelo}</p>
                                    ${result.data.vehiculo_placa ? `<p><strong>Placa:</strong> ${result.data.vehiculo_placa}</p>` : ''}
                                </div>
                            `,
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        });

                        // Recargar la página para ver los cambios
                        location.reload();

                    } catch (error) {
                        console.error('Error:', error);
                        await swalInstance.close();

                        let errorMessage = 'Ocurrió un error al procesar tu cita.';
                        let errorDetails = '';

                        if (error.message) {
                            if (typeof error.message === 'string') {
                                errorMessage = error.message;

                                if (error.message.includes('No atendemos domingos')) {
                                    errorMessage =
                                        'No trabajamos los domingos. Por favor selecciona otro día.';
                                    await swalWithBootstrapButtons.fire({
                                        title: 'Domingo no laborable',
                                        text: errorMessage,
                                        icon: 'warning',
                                        confirmButtonColor: '#4facfe'
                                    });
                                    return;
                                } else if (error.message.includes('horario ya está ocupado') || error
                                    .message.includes('horario seleccionado está ocupado')) {
                                    errorMessage =
                                        'Lo sentimos, ese horario ya está ocupado. Por favor selecciona otro horario.';
                                }
                            } else if (error.message.message) {
                                errorMessage = error.message.message;
                                if (error.message.errors) {
                                    errorDetails = Object.values(error.message.errors).join('<br>');
                                }
                            }
                        }

                        const errorHtml = `
                            <div style="text-align: left;">
                                <p>${errorMessage}</p>
                                ${errorDetails ? `<p style="color: #dc3545; margin-top: 10px;">${errorDetails}</p>` : ''}
                                <p style="margin-top: 10px; font-size: 0.9em; color: #666;">
                                    Por favor intenta nuevamente con un horario diferente.
                                </p>
                            </div>
                        `;

                        await swalWithBootstrapButtons.fire({
                            title: isEdit ? 'Error al actualizar' : 'Error al agendar',
                            html: errorHtml,
                            icon: 'error',
                            confirmButtonColor: '#ff6b6b'
                        });
                    }
                });
            }
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-warning')) {
                const citaId = e.target.closest('.cita-card').dataset.citaId;
                editCita(citaId);
            }
            if (e.target.closest('.btn-outline')) {
                const citaId = e.target.closest('.cita-card').dataset.citaId;
                cancelCita(citaId);
            }
        });

        // Función para limpiar filtros
        function limpiarFiltros() {
            // Resetear el formulario
            document.getElementById('filtrosForm').reset();

            // Redirigir a la URL base sin parámetros
            window.location.href = '{{ route('cliente.citas') }}';
        }

        // Función para aplicar filtros automáticamente
        function aplicarFiltros() {
            // Mostrar overlay de carga
            const loadingOverlay = document.createElement('div');
            loadingOverlay.className = 'loading-overlay';
            loadingOverlay.innerHTML = '<div class="loading-spinner"></div>';
            document.body.appendChild(loadingOverlay);

            // Obtener valores actuales
            const estado = document.getElementById('estado').value;
            const fecha_desde = document.getElementById('fecha_desde').value;
            const fecha_hasta = document.getElementById('fecha_hasta').value;
            const vehiculo_id = document.getElementById('vehiculo_id').value;

            // Construir URL con parámetros
            let url = '{{ route('cliente.citas') }}?';

            if (estado) url += `estado=${estado}&`;
            if (fecha_desde) url += `fecha_desde=${fecha_desde}&`;
            if (fecha_hasta) url += `fecha_hasta=${fecha_hasta}&`;
            if (vehiculo_id) url += `vehiculo_id=${vehiculo_id}&`;

            // Eliminar el último & si existe
            url = url.replace(/&$/, '');

            // Recargar la página con los nuevos parámetros
            window.location.href = url;
        }

        // Configurar event listeners para los filtros
        document.addEventListener('DOMContentLoaded', function() {
            const filters = ['estado', 'vehiculo_id', 'fecha_desde', 'fecha_hasta'];

            filters.forEach(filterId => {
                const element = document.getElementById(filterId);
                if (element) {
                    element.addEventListener('change', aplicarFiltros);
                }
            });

            // Animación de entrada para las cards
            const citaCards = document.querySelectorAll('.cita-card');
            citaCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('createCitaModal');
            if (event.target == modal) {
                closeCitaModal();
            }
        }
    </script>
</body>

</html>
