<?php

namespace App\Console\Commands;

use App\Models\SimuladoAssignment;
use App\Models\User;
use App\Models\Simulado;
use App\Models\SimuladoAttempt;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendSimuladoDeadlineReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulado:send-deadline-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia lembretes de prazo para simulados próximos do vencimento';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando envio de lembretes de prazo...');
        
        $notificationService = app(NotificationService::class);
        $now = Carbon::now();
        $reminderDays = [7, 3, 1]; // Enviar lembretes 7, 3 e 1 dia antes
        
        $totalSent = 0;
        
        foreach ($reminderDays as $days) {
            $targetDate = $now->copy()->addDays($days)->startOfDay();
            $endDate = $targetDate->copy()->endOfDay();
            
            // Buscar atribuições que vencem no período alvo
            $assignments = SimuladoAssignment::whereBetween('due_at', [$targetDate, $endDate])
                ->with(['simulado'])
                ->get();
            
            $this->info("Processando lembretes para {$days} dia(s): {$assignments->count()} atribuições encontradas");
            
            foreach ($assignments as $assignment) {
                $users = $this->getUsersFromAssignment($assignment);
                
                foreach ($users as $user) {
                    // Verificar se o usuário já completou o simulado
                    $hasCompleted = SimuladoAttempt::where('simulado_id', $assignment->simulado_id)
                        ->where('user_id', $user->id)
                        ->whereNotNull('submitted_at')
                        ->exists();
                    
                    if (!$hasCompleted) {
                        try {
                            $notificationService->simuladoDeadlineReminder(
                                $user,
                                $assignment->simulado,
                                $assignment->due_at,
                                $days
                            );
                            $totalSent++;
                        } catch (\Exception $e) {
                            $this->error("Erro ao enviar lembrete para usuário {$user->id}: {$e->getMessage()}");
                        }
                    }
                }
            }
        }
        
        $this->info("Concluído! Total de lembretes enviados: {$totalSent}");
        return Command::SUCCESS;
    }
    
    /**
     * Obtém usuários de uma atribuição baseado no tipo de alvo
     */
    private function getUsersFromAssignment(SimuladoAssignment $assignment): \Illuminate\Support\Collection
    {
        switch ($assignment->target_type) {
            case 'user':
                $user = User::find($assignment->target_id);
                return $user ? collect([$user]) : collect();
                
            case 'course':
                // TODO: Implementar lógica para obter usuários de um curso
                // return User::whereHas('courses', function($q) use ($assignment) {
                //     $q->where('course_id', $assignment->target_id);
                // })->get();
                return collect();
                
            case 'class':
                // TODO: Implementar lógica para obter usuários de uma turma
                // return User::whereHas('classes', function($q) use ($assignment) {
                //     $q->where('class_id', $assignment->target_id);
                // })->get();
                return collect();
                
            default:
                return collect();
        }
    }
}