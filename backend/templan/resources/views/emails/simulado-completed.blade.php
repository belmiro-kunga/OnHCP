@extends('emails.layout')

@section('title', 'Simulado Concluído - OnHCP')

@section('header', 'Simulado Concluído')

@section('subtitle', 'Parabéns! Você concluiu seu simulado')

@section('content')
    <div class="notification-type type-completed">
        Simulado Concluído
    </div>

    <p>Olá, <strong>{{ $user->name }}</strong>!</p>

    <p>🎉 <strong>Parabéns!</strong> Você concluiu com sucesso o simulado. Seu empenho e dedicação são admiráveis!</p>

    <div class="simulado-info">
        <h3>{{ $simulado->title }}</h3>
        
        @if(isset($data['completed_at']))
            <p><strong>Concluído em:</strong> {{ \Carbon\Carbon::parse($data['completed_at'])->format('d/m/Y H:i') }}</p>
        @endif
        
        @if(isset($data['time_spent']))
            <p><strong>Tempo Utilizado:</strong> {{ $data['time_spent'] }}</p>
        @endif
        
        @if(isset($data['total_questions']))
            <p><strong>Total de Questões:</strong> {{ $data['total_questions'] }}</p>
        @endif
        
        @if(isset($data['submission_time']))
            <p><strong>Enviado em:</strong> {{ \Carbon\Carbon::parse($data['submission_time'])->format('d/m/Y H:i') }}</p>
        @endif
        
        @if(isset($data['due_date']))
            @php
                $wasOnTime = \Carbon\Carbon::parse($data['submission_time'] ?? $data['completed_at'])->lte(\Carbon\Carbon::parse($data['due_date']));
            @endphp
            <p><strong>Status do Prazo:</strong> 
                <span style="color: {{ $wasOnTime ? '#28a745' : '#dc3545' }}; font-weight: 600;">
                    {{ $wasOnTime ? '✅ Entregue no prazo' : '⚠️ Entregue após o prazo' }}
                </span>
            </p>
        @endif
    </div>

    @if(isset($data['due_date']))
        @php
            $wasOnTime = \Carbon\Carbon::parse($data['submission_time'] ?? $data['completed_at'])->lte(\Carbon\Carbon::parse($data['due_date']));
        @endphp
        
        @if($wasOnTime)
            <div style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 4px; margin: 15px 0;">
                <strong>🎯 Excelente!</strong><br>
                Você conseguiu concluir o simulado dentro do prazo estabelecido. Isso demonstra sua organização e comprometimento!
            </div>
        @else
            <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 4px; margin: 15px 0;">
                <strong>⏰ Atenção ao prazo!</strong><br>
                O simulado foi entregue após o prazo. Para os próximos, tente se organizar melhor para entregar dentro do tempo estabelecido.
            </div>
        @endif
    @endif

    <div style="background-color: #e3f2fd; padding: 20px; border-radius: 4px; margin: 20px 0; text-align: center;">
        <h4 style="margin: 0 0 15px 0; color: #1976d2;">📋 O que acontece agora?</h4>
        <div style="display: flex; flex-wrap: wrap; justify-content: space-around; gap: 15px;">
            <div style="flex: 1; min-width: 200px; background: white; padding: 15px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <div style="font-size: 24px; margin-bottom: 10px;">📊</div>
                <strong>Correção</strong><br>
                <small>Seu simulado será corrigido automaticamente</small>
            </div>
            <div style="flex: 1; min-width: 200px; background: white; padding: 15px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <div style="font-size: 24px; margin-bottom: 10px;">📈</div>
                <strong>Resultado</strong><br>
                <small>Você receberá o resultado em breve</small>
            </div>
            <div style="flex: 1; min-width: 200px; background: white; padding: 15px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <div style="font-size: 24px; margin-bottom: 10px;">💬</div>
                <strong>Feedback</strong><br>
                <small>Análise detalhada do seu desempenho</small>
            </div>
        </div>
    </div>

    @if(isset($data['estimated_result_time']))
        <p style="text-align: center; background-color: #f8f9fa; padding: 15px; border-radius: 4px; margin: 20px 0;">
            <strong>⏱️ Previsão de Resultado:</strong><br>
            O resultado estará disponível até <strong>{{ \Carbon\Carbon::parse($data['estimated_result_time'])->format('d/m/Y H:i') }}</strong>
            <br><small>({{ \Carbon\Carbon::parse($data['estimated_result_time'])->diffForHumans() }})</small>
        </p>
    @endif

    <p style="text-align: center;">Enquanto aguarda o resultado, que tal acessar a plataforma?</p>

    <div style="text-align: center;">
        <a href="{{ config('app.url') }}/dashboard" class="action-button">
            🏠 Acessar Dashboard
        </a>
    </div>

    @if(isset($data['next_steps']) && is_array($data['next_steps']))
        <div style="background-color: #fff3e0; padding: 15px; border-radius: 4px; margin: 20px 0; border-left: 4px solid #ff9800;">
            <h4 style="margin: 0 0 10px 0; color: #f57c00;">🎯 Próximos Passos Recomendados:</h4>
            <ul style="margin: 0; padding-left: 20px; color: #333;">
                @foreach($data['next_steps'] as $step)
                    <li>{{ $step }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(isset($data['study_materials']))
        <div style="background-color: #e8f5e8; padding: 15px; border-radius: 4px; margin: 20px 0;">
            <h4 style="margin: 0 0 10px 0; color: #388e3c;">📚 Material de Estudo Recomendado:</h4>
            <p style="margin: 0; color: #333;">{{ $data['study_materials'] }}</p>
        </div>
    @endif

    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 4px; margin: 30px 0; text-align: center; border: 2px dashed #dee2e6;">
        <h4 style="margin: 0 0 15px 0; color: #333;">🌟 Reflexão</h4>
        <p style="margin: 0; color: #666; font-style: italic;">
            "O sucesso não é final, o fracasso não é fatal: é a coragem de continuar que conta."<br>
            <small>- Winston Churchill</small>
        </p>
        <p style="margin: 15px 0 0 0; color: #333;">
            Continue se dedicando aos estudos. Cada simulado é uma oportunidade de crescimento!
        </p>
    </div>

    @if(isset($data['instructor_message']))
        <div style="background-color: #e3f2fd; padding: 15px; border-radius: 4px; margin: 20px 0; border-left: 4px solid #2196f3;">
            <h4 style="margin: 0 0 10px 0; color: #1976d2;">💬 Mensagem do Instrutor:</h4>
            <p style="margin: 0; color: #333; font-style: italic;">"{{ $data['instructor_message'] }}"</p>
        </div>
    @endif

    <p style="color: #666; font-size: 14px; margin-top: 30px; text-align: center;">
        <strong>Obrigado por usar a plataforma OnHCP!</strong><br>
        Continuamos trabalhando para oferecer a melhor experiência de aprendizado.
    </p>
@endsection