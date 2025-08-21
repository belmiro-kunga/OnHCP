@extends('emails.layout')

@section('title', 'Novo Simulado Atribuído - OnHCP')

@section('header', 'Novo Simulado Atribuído')

@section('subtitle', 'Um novo simulado foi atribuído para você')

@section('content')
    <div class="notification-type type-assigned">
        Simulado Atribuído
    </div>

    <p>Olá, <strong>{{ $user->name }}</strong>!</p>

    <p>Um novo simulado foi atribuído para você na plataforma OnHCP. Confira os detalhes abaixo:</p>

    <div class="simulado-info {{ $priority === 'urgent' ? 'priority-urgent' : ($priority === 'high' ? 'priority-high' : '') }}">
        <h3>{{ $simulado->title }}</h3>
        
        @if($simulado->description)
            <p><strong>Descrição:</strong> {{ $simulado->description }}</p>
        @endif
        
        @if(isset($data['due_date']))
            <p><strong>Prazo de Entrega:</strong> 
                <span style="color: {{ \Carbon\Carbon::parse($data['due_date'])->diffInDays(now()) <= 1 ? '#dc3545' : '#666' }};">
                    {{ \Carbon\Carbon::parse($data['due_date'])->format('d/m/Y H:i') }}
                    ({{ \Carbon\Carbon::parse($data['due_date'])->diffForHumans() }})
                </span>
            </p>
        @endif
        
        @if($simulado->duration_minutes)
            <p><strong>Duração:</strong> {{ $simulado->duration_minutes }} minutos</p>
        @endif
        
        @if($simulado->total_questions)
            <p><strong>Total de Questões:</strong> {{ $simulado->total_questions }}</p>
        @endif
        
        @if(isset($data['assigned_by']))
            <p><strong>Atribuído por:</strong> {{ $data['assigned_by'] }}</p>
        @endif
    </div>

    @if(isset($data['due_date']) && \Carbon\Carbon::parse($data['due_date'])->diffInDays(now()) <= 3)
        <div class="deadline-warning {{ \Carbon\Carbon::parse($data['due_date'])->diffInDays(now()) <= 1 ? 'deadline-urgent' : '' }}">
            <strong>⚠️ Atenção:</strong> 
            @if(\Carbon\Carbon::parse($data['due_date'])->diffInDays(now()) <= 1)
                Este simulado tem prazo de entrega em menos de 24 horas!
            @else
                Este simulado tem prazo de entrega em {{ \Carbon\Carbon::parse($data['due_date'])->diffInDays(now()) }} dias.
            @endif
        </div>
    @endif

    <p>Para iniciar o simulado, clique no botão abaixo:</p>

    <a href="{{ config('app.url') }}/simulados/{{ $simulado->id }}" class="action-button">
        🎯 Iniciar Simulado
    </a>

    <p style="margin-top: 30px; color: #666; font-size: 14px;">
        <strong>Dicas importantes:</strong><br>
        • Certifique-se de ter uma conexão estável com a internet<br>
        • Reserve um ambiente tranquilo para realizar o simulado<br>
        • Leia atentamente todas as instruções antes de começar<br>
        @if(isset($data['due_date']))
            • Lembre-se do prazo de entrega: {{ \Carbon\Carbon::parse($data['due_date'])->format('d/m/Y H:i') }}
        @endif
    </p>

    @if(isset($data['instructions']))
        <div style="background-color: #e3f2fd; padding: 15px; border-radius: 4px; margin: 20px 0;">
            <h4 style="margin: 0 0 10px 0; color: #1976d2;">📋 Instruções Especiais:</h4>
            <p style="margin: 0; color: #333;">{{ $data['instructions'] }}</p>
        </div>
    @endif
@endsection