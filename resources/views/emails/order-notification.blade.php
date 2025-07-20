<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Pedido - RMateriais</title>
    <style>
        :root {
            --background: oklch(0.9777 0.0041 301.4256);
            --foreground: oklch(0.3651 0.0325 287.0807);
            --card: oklch(1.0000 0 0);
            --card-foreground: oklch(0.3651 0.0325 287.0807);
            --primary: oklch(0.6104 0.0767 299.7335);
            --primary-foreground: oklch(0.9777 0.0041 301.4256);
            --secondary: oklch(0.8957 0.0265 300.2416);
            --secondary-foreground: oklch(0.3651 0.0325 287.0807);
            --muted: oklch(0.8906 0.0139 299.7754);
            --muted-foreground: oklch(0.5288 0.0375 290.7895);
            --accent: oklch(0.7889 0.0802 359.9375);
            --border: oklch(0.8447 0.0226 300.1421);
            --radius: 0.5rem;
            --shadow-sm: 1px 2px 5px 1px hsl(0 0% 0% / 0.06), 1px 1px 2px 0px hsl(0 0% 0% / 0.06);
            --shadow-md: 1px 2px 5px 1px hsl(0 0% 0% / 0.06), 1px 2px 4px 0px hsl(0 0% 0% / 0.06);
            --font-sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-sans);
            line-height: 1.6;
            color: var(--foreground);
            background-color: var(--background);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            padding: 0;
            margin: 0;
        }

        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background: var(--card);
            border-radius: calc(var(--radius) + 4px);
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        .header {
            background: var(--primary);
            color: var(--primary-foreground);
            padding: 32px 24px;
            text-align: left;
        }

        .header-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .header-text h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .header-text p {
            font-size: 0.875rem;
            opacity: 0.9;
            font-weight: 400;
        }

        .content {
            padding: 32px 24px;
        }

        .section {
            margin-bottom: 32px;
        }

        .section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--card-foreground);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--muted-foreground);
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .info-value {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--card-foreground);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            background: var(--secondary);
            color: var(--secondary-foreground);
            padding: 4px 12px;
            border-radius: calc(var(--radius) - 2px);
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .materials-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .material-card {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 16px;
            background: var(--card);
        }

        .material-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .material-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--card-foreground);
        }

        .material-quantity {
            display: flex;
            background: var(--muted);
            color: var(--muted-foreground);
            padding: 2px 8px;
            border-radius: calc(var(--radius) - 4px);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .material-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .material-detail {
            font-size: 0.75rem;
            color: var(--muted-foreground);
        }

        .material-detail strong {
            color: var(--card-foreground);
            font-weight: 500;
        }

        .summary-card {
            background: var(--primary);
            color: var(--primary-foreground);
            padding: 24px;
            border-radius: var(--radius);
            text-align: center;
        }

        .summary-label {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-bottom: 8px;
        }

        .summary-number {
            font-size: 2.25rem;
            font-weight: 700;
            line-height: 1;
        }

        .footer {
            background: var(--muted);
            padding: 24px;
            text-align: center;
            border-top: 1px solid var(--border);
        }

        .footer-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--card-foreground);
            margin-bottom: 8px;
        }

        .footer-text {
            font-size: 0.875rem;
            color: var(--muted-foreground);
            margin-bottom: 16px;
        }

        .action-button {
            display: inline-flex;
            align-items: center;
            background: var(--primary);
            color: var(--primary-foreground);
            padding: 10px 20px;
            border-radius: var(--radius);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: opacity 0.2s ease;
        }

        .action-button:hover {
            opacity: 0.9;
        }

        .divider {
            height: 1px;
            background: var(--border);
            margin: 24px 0;
        }

        @media (max-width: 640px) {
            .email-container {
                margin: 0;
                border-radius: 0;
            }

            .header {
                padding: 24px 16px;
            }

            .content {
                padding: 24px 16px;
            }

            .footer {
                padding: 24px 16px;
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .material-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .material-details {
                grid-template-columns: 1fr;
                gap: 6px;
            }

            .summary-number {
                font-size: 1.875rem;
            }
        }
    </style>
</head>
<body>
<div class="email-container">
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="header-icon">📋</div>
            <div class="header-text">
                <h1>Novo Pedido de Material</h1>
                <p>RMateriais • Sistema de Gestão</p>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="content">
        <!-- Order Information -->
        <div class="section">
            <div class="section-title">Informações do Pedido</div>
            <div class="info-card">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Aluno</div>
                        <div class="info-value">{{ $order->user->name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ID do Pedido</div>
                        <div class="info-value">#{{ $order->id }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Data do Pedido</div>
                        <div class="info-value">{{ $order->requested_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="status-badge">Pendente</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Materials -->
        <div class="section">
            <div class="section-title">Materiais Requisitados</div>
            <div class="materials-list">
                @foreach($order->requestItems as $item)
                    <div class="material-card">
                        <div class="material-header">
                            <div class="material-name">{{ $item->material->name }}</div>
                            <div class="material-quantity">{{ $item->quantity }}x</div>
                        </div>
                        <div class="material-details">
                            <div class="material-detail">
                                <strong>Categoria:</strong> {{ $item->material->category->name }}
                            </div>
                            <div class="material-detail">
                                <strong>Devolução:</strong> {{ $item->due_date->format('d/m/Y') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Summary -->
        <div class="section">
            <div class="summary-card">
                <div class="summary-label">Total de Itens</div>
                <div class="summary-number">{{ $order->requestItems->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-title">Ação Necessária</div>
        <div class="footer-text">Este pedido aguarda aprovação no sistema RMateriais.</div>
        <a href="#" class="action-button">Aceder ao Sistema</a>
    </div>
</div>
</body>
</html>
