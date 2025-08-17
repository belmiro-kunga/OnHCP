<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Envia um email simples para a lista de administradores definida em NOTIFY_ADMIN_EMAILS
     * NOTIFY_ADMIN_EMAILS=admin1@example.com,admin2@example.com
     */
    public function notifyAdmins(string $subject, string $body): void
    {
        $list = trim((string) env('NOTIFY_ADMIN_EMAILS', ''));
        if ($list === '') {
            return; // sem destinatários configurados
        }
        $emails = array_filter(array_map('trim', explode(',', $list)));
        if (empty($emails)) {
            return;
        }

        foreach ($emails as $email) {
            // Envio simples em texto puro para evitar criar Mailables agora
            Mail::raw($body, function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });
        }
    }

    // ====== Approvals ======
    /** Pedido de aprovação criado */
    public function approvalRequested(string $requestId, string $requester, string $target, string $changesSummary): void
    {
        $subject = sprintf('[Approvals] Pedido #%s criado por %s', $requestId, $requester);
        $body = "Pedido de aprovação criado.\nRequester: $requester\nTarget: $target\nAlterações: $changesSummary\nID: $requestId";
        $this->notifyAdmins($subject, $body);
    }
    /** Pedido aprovado */
    public function approvalApproved(string $requestId, string $approver, string $target): void
    {
        $subject = sprintf('[Approvals] Pedido #%s APROVADO', $requestId);
        $body = "Pedido aprovado.\nAprovador: $approver\nTarget: $target\nID: $requestId";
        $this->notifyAdmins($subject, $body);
    }
    /** Pedido rejeitado */
    public function approvalRejected(string $requestId, string $approver, string $target, ?string $reason = null): void
    {
        $subject = sprintf('[Approvals] Pedido #%s REJEITADO', $requestId);
        $body = "Pedido rejeitado.\nAprovador: $approver\nTarget: $target\nMotivo: " . ($reason ?: '-') . "\nID: $requestId";
        $this->notifyAdmins($subject, $body);
    }

    // ====== Delegations ======
    /** Delegação criada */
    public function delegationCreated(string $delegationId, string $delegator, string $delegatee, string $scope, string $startsAt, string $endsAt): void
    {
        $subject = sprintf('[Delegations] Delegação #%s criada', $delegationId);
        $body = "Delegação criada.\nDelegador: $delegator\nDelegado: $delegatee\nEscopo: $scope\nInício: $startsAt\nFim: $endsAt\nID: $delegationId";
        $this->notifyAdmins($subject, $body);
    }
    /** Delegação expirada */
    public function delegationExpired(string $delegationId, string $delegator, string $delegatee, string $endsAt): void
    {
        $subject = sprintf('[Delegations] Delegação #%s expirada', $delegationId);
        $body = "Delegação expirada.\nDelegador: $delegator\nDelegado: $delegatee\nFim: $endsAt\nID: $delegationId";
        $this->notifyAdmins($subject, $body);
    }
    /** Delegação revogada */
    public function delegationRevoked(string $delegationId, string $revoker, string $delegator, string $delegatee): void
    {
        $subject = sprintf('[Delegations] Delegação #%s revogada', $delegationId);
        $body = "Delegação revogada.\nRevogador: $revoker\nDelegador: $delegator\nDelegado: $delegatee\nID: $delegationId";
        $this->notifyAdmins($subject, $body);
    }

    // ====== Licenses ======
    /** Licença atribuída */
    public function licenseAssigned(string $licenseId, string $product, string $userEmail): void
    {
        $subject = sprintf('[Licenses] Licença %s atribuída a %s', $product, $userEmail);
        $body = "Licença atribuída.\nProduto: $product\nUtilizador: $userEmail\nLicense ID: $licenseId";
        $this->notifyAdmins($subject, $body);
    }
    /** Licença removida */
    public function licenseUnassigned(string $licenseId, string $product, string $userEmail): void
    {
        $subject = sprintf('[Licenses] Licença %s removida de %s', $product, $userEmail);
        $body = "Licença removida.\nProduto: $product\nUtilizador: $userEmail\nLicense ID: $licenseId";
        $this->notifyAdmins($subject, $body);
    }
    /** Limite de licenças atingido */
    public function licenseLimitReached(string $product, int $seatsTotal): void
    {
        $subject = sprintf('[Licenses] Limite atingido para %s', $product);
        $body = "Limite de licenças atingido.\nProduto: $product\nVagas totais: $seatsTotal";
        $this->notifyAdmins($subject, $body);
    }
}
