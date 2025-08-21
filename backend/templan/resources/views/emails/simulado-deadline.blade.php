@extends('emails.layout')

@section('title', 'Lembrete de Prazo - OnHCP')

@section('header', 'Lembrete de Prazo')

@section('subtitle', 'Seu simulado está próximo do prazo de entrega')

@section('content')
    @php
        $daysLeft = \Carbon\Carbon::parse($data['due_date'])->diffInDays(now());
        $isUrgent = $daysLeft <= 1;
        $isWarning = $daysLeft <= 3;
    @endphp

    <div class="notification-type type-deadline">
        Lembrete de Prazo
    </div>

    <p>Olá, <strong>{{ $user->name }}</strong>!</p>

    @if($isUrgent)
        <p style="color: #dc3545; font-weight: 600;">⚠️ <strong>URGENTE:</strong> Seu simulado tem prazo de entrega em menos de 24 horas!</p>
    @elseif($daysLeft == 1)
        <p style="color: #fd7e14; font-weight: 600;">⏰ Seu simulado tem prazo de entrega amanhã!</p>
    @else
        <p>Este é um lembrete de que você tem um simulado com prazo de entrega em <strong>{{ $daysLeft }} dias</strong>.</p>
    @endif

    <div class="simulado-info {{ $isUrgent ? 'priority-urgent' : ($isWarning ? 'priority-high' : '') }}">
        <h3>{{ $simulado->title }}</h3>
        
        <p><strong>Prazo de Entrega:</strong> 
            <span style="color: {{ $isUrgent ? '#dc3545' : ($isWarning ? '#fd7e14' : '#666') }}; font-weight: 600;">
                {{ \Carbon\Carbon::parse($data['due_date'])->format('d/m/Y H:i') }}
                ({{ \Carbon\Carbon::parse($data['due_date'])->diffForHumans() }})
            </span>
        </p>
        
        @if($simulado->duration_minutes)
            <p><strong>Duração Estimada:</strong> {{ $simulado->duration_minutes }} minutos</p>
        @endif
        
        @if($simulado->total_questions)
            <p><strong>Total de Questões:</strong> {{ $simulado->total_questions }}</p>
        @endif
        
        @if(isset($data['progress']))
            <p><strong>Progresso:</strong> 
                @if($data['progress'] == 0)
                    <span style="color: #dc3545;">Não iniciado</span>
                @elseif($data['progress'] < 100)
                    <span style="color: #fd7e14;">{{ $data['progress'] }}% concluído</span>
                @else
                    <span style="color: #28a745;">Concluído</span>
                @endif
            </p>
        @endif
    </div>

    @if($isUrgent)
        <div class="deadline-warning deadline-urgent">
            <strong>🚨 AÇÃO NECESSÁRIA:</strong> 
            Este simulado deve ser concluído até {{ \Carbon\Carbon::parse($data['due_date'])->format('d/m/Y H:i') }}. 
            Não perca o prazo!
        </div>
    @elseif($isWarning)
        <div class="deadline-warning">
            <strong>⚠️ Prazo Próximo:</strong> 
            Você ainda tem {{ $daysLeft }} dias para concluir este simulado. 
            Recomendamos que organize seu tempo para não perder o prazo.
        </div>
    @endif

    @if(isset($data['progress']) && $data['progress'] == 0)
        <p>Você ainda não iniciou este simulado. Clique no botão abaixo para começar:</p>
        
        <a href="{{ config('app.url') }}/simulados/{{ $simulado->id }}" class="action-button">
            🚀 Iniciar Simulado Agora
        </a>
    @elseif(isset($data['progress']) && $data['progress'] < 100)
        <p>Você já iniciou este simulado. Continue de onde parou:</p>
        
        <a href="{{ config('app.url') }}/simulados/{{ $simulado->id }}/continue" class="action-button">
            ▶️ Continuar Simulado
        </a>
    @else
        <p>Acesse o simulado para revisar ou finalizar:</p>
        
        <a href="{{ config('app.url') }}/simulados/{{ $simulado->id }}" class="action-button">
            📝 Acessar Simulado
        </a>
    @endif

    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 4px; margin: 20px 0; border-left: 4px solid #17a2b8;">
        <h4 style="margin: 0 0 10px 0; color: #17a2b8;">💡 Dicas para Otimizar seu Tempo:</h4>
        <ul style="margin: 0; padding-left: 20px; color: #333;">
            @if($simulado->duration_minutes)
                <li>Reserve pelo menos {{ $simulado->duration_minutes }} minutos ininterruptos</li>
            @endif
            <li>Escolha um ambiente silencioso e sem distrações</li>
            <li>Tenha uma conexão estável com a internet</li>
            <li>Leia todas as questões com atenção antes de responder</li>
            @if($isWarning)
                <li><strong>Não deixe para a última hora!</strong></li>
            @endif
        </ul>
    </div>

    @if(isset($data['support_contact']))
        <p style="color: #666; font-size: 14px; margin-top: 30px;">
            <strong>Precisa de ajuda?</strong><br>
            Entre em contato conosco: {{ $data['support_contact'] }}
        </p>
    @endif
@endsection