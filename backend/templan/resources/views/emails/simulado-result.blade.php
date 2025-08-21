@extends('emails.layout')

@section('title', 'Resultado Disponível - OnHCP')

@section('header', 'Resultado Disponível')

@section('subtitle', 'O resultado do seu simulado está pronto')

@section('content')
    <div class="notification-type type-result">
        Resultado Disponível
    </div>

    <p>Olá, <strong>{{ $user->name }}</strong>!</p>

    <p>O resultado do seu simulado já está disponível! Confira seu desempenho e veja onde pode melhorar.</p>

    <div class="simulado-info">
        <h3>{{ $simulado->title }}</h3>
        
        @if(isset($data['completed_at']))
            <p><strong>Concluído em:</strong> {{ \Carbon\Carbon::parse($data['completed_at'])->format('d/m/Y H:i') }}</p>
        @endif
        
        @if(isset($data['score']))
            <p><strong>Pontuação:</strong> 
                <span style="font-size: 18px; font-weight: 600; color: {{ $data['score'] >= 70 ? '#28a745' : ($data['score'] >= 50 ? '#fd7e14' : '#dc3545') }};">
                    {{ $data['score'] }}%
                </span>
            </p>
        @endif
        
        @if(isset($data['correct_answers']) && isset($data['total_questions']))
            <p><strong>Acertos:</strong> {{ $data['correct_answers'] }} de {{ $data['total_questions'] }} questões</p>
        @endif
        
        @if(isset($data['time_spent']))
            <p><strong>Tempo Utilizado:</strong> {{ $data['time_spent'] }}</p>
        @endif
        
        @if(isset($data['grade']))
            <p><strong>Conceito:</strong> 
                <span style="font-weight: 600; color: {{ $data['grade'] === 'A' ? '#28a745' : ($data['grade'] === 'B' ? '#17a2b8' : ($data['grade'] === 'C' ? '#fd7e14' : '#dc3545')) }};">
                    {{ $data['grade'] }}
                </span>
            </p>
        @endif
    </div>

    @if(isset($data['score']))
        @if($data['score'] >= 80)
            <div style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 4px; margin: 15px 0;">
                <strong>🎉 Excelente desempenho!</strong><br>
                Parabéns! Você demonstrou um ótimo domínio do conteúdo.
            </div>
        @elseif($data['score'] >= 60)
            <div style="background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 4px; margin: 15px 0;">
                <strong>👍 Bom desempenho!</strong><br>
                Você está no caminho certo. Continue estudando para melhorar ainda mais.
            </div>
        @else
            <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 4px; margin: 15px 0;">
                <strong>📚 Continue estudando!</strong><br>
                Não desanime! Use este resultado como motivação para se dedicar mais aos estudos.
            </div>
        @endif
    @endif

    <p>Clique no botão abaixo para ver o resultado detalhado:</p>

    <a href="{{ config('app.url') }}/simulados/{{ $simulado->id }}/resultado" class="action-button">
        📊 Ver Resultado Completo
    </a>

    @if(isset($data['performance_by_subject']) && is_array($data['performance_by_subject']))
        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 4px; margin: 20px 0;">
            <h4 style="margin: 0 0 15px 0; color: #333;">📈 Desempenho por Matéria:</h4>
            @foreach($data['performance_by_subject'] as $subject => $performance)
                <div style="margin-bottom: 10px;">
                    <strong>{{ $subject }}:</strong> 
                    <span style="color: {{ $performance >= 70 ? '#28a745' : ($performance >= 50 ? '#fd7e14' : '#dc3545') }};">
                        {{ $performance }}%
                    </span>
                </div>
            @endforeach
        </div>
    @endif

    @if(isset($data['feedback']))
        <div style="background-color: #e3f2fd; padding: 15px; border-radius: 4px; margin: 20px 0; border-left: 4px solid #2196f3;">
            <h4 style="margin: 0 0 10px 0; color: #1976d2;">💬 Feedback do Professor:</h4>
            <p style="margin: 0; color: #333; font-style: italic;">"{{ $data['feedback'] }}"</p>
        </div>
    @endif

    <div style="background-color: #fff3e0; padding: 15px; border-radius: 4px; margin: 20px 0; border-left: 4px solid #ff9800;">
        <h4 style="margin: 0 0 10px 0; color: #f57c00;">🎯 Próximos Passos:</h4>
        <ul style="margin: 0; padding-left: 20px; color: #333;">
            <li>Revise as questões que você errou</li>
            <li>Estude os tópicos com menor desempenho</li>
            @if(isset($data['score']) && $data['score'] < 70)
                <li>Considere refazer simulados similares</li>
                <li>Busque material complementar sobre os temas</li>
            @endif
            <li>Continue praticando regularmente</li>
        </ul>
    </div>

    @if(isset($data['next_simulado']))
        <div style="background-color: #e8f5e8; padding: 15px; border-radius: 4px; margin: 20px 0;">
            <h4 style="margin: 0 0 10px 0; color: #388e3c;">🚀 Próximo Desafio:</h4>
            <p style="margin: 0; color: #333;">
                Que tal tentar o próximo simulado: <strong>{{ $data['next_simulado'] }}</strong>?
            </p>
        </div>
    @endif

    <p style="color: #666; font-size: 14px; margin-top: 30px;">
        <strong>Lembre-se:</strong> O importante não é apenas a nota, mas o aprendizado. 
        Use este resultado para identificar pontos de melhoria e continue se dedicando aos estudos!
    </p>
@endsection