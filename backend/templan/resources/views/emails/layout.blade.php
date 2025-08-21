<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'OnHCP - Notificação')</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 300;
        }
        .content {
            padding: 30px 20px;
        }
        .notification-type {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .type-assigned {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        .type-deadline {
            background-color: #fff3e0;
            color: #f57c00;
        }
        .type-result {
            background-color: #e8f5e8;
            color: #388e3c;
        }
        .type-completed {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }
        .simulado-info {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 4px 4px 0;
        }
        .simulado-info h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 18px;
        }
        .simulado-info p {
            margin: 5px 0;
            color: #666;
        }
        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            margin: 20px 0;
            transition: transform 0.2s;
        }
        .action-button:hover {
            transform: translateY(-2px);
            text-decoration: none;
            color: white;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        .priority-high {
            border-left-color: #dc3545;
        }
        .priority-urgent {
            border-left-color: #fd7e14;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { border-left-color: #fd7e14; }
            50% { border-left-color: #ff9800; }
            100% { border-left-color: #fd7e14; }
        }
        .deadline-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .deadline-urgent {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px;
            }
            .header {
                padding: 20px 15px;
            }
            .content {
                padding: 20px 15px;
            }
            .action-button {
                display: block;
                text-align: center;
                margin: 20px 0;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>@yield('header', 'OnHCP')</h1>
            @hasSection('subtitle')
                <p style="margin: 10px 0 0 0; opacity: 0.9;">@yield('subtitle')</p>
            @endif
        </div>
        
        <div class="content">
            @yield('content')
        </div>
        
        <div class="footer">
            <p>Esta é uma notificação automática do sistema OnHCP.</p>
            <p>
                <a href="{{ config('app.url') }}">Acessar Plataforma</a> |
                <a href="{{ config('app.url') }}/notification-preferences">Configurar Notificações</a>
            </p>
            <p style="margin-top: 15px; font-size: 12px; color: #999;">
                © {{ date('Y') }} OnHCP. Todos os direitos reservados.
            </p>
        </div>
    </div>
</body>
</html>