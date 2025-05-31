<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\PlayerCycleSummary;
use Authorization\IdentityInterface;

class PlayerCycleSummaryPolicy
{
    /**
     * Verifica se o usuário pode visualizar um resumo de ciclo específico.
     *
     * @param \Authorization\IdentityInterface $user O usuário logado.
     * @param \App\Model\Entity\PlayerCycleSummary $playerCycleSummary A entidade do resumo.
     * @return bool
     */
    public function canView(IdentityInterface $user, PlayerCycleSummary $playerCycleSummary)
    {
        // Permite que qualquer usuário logado visualize.
        return true; // Ou return $user !== null;
    }

    /**
     * Verifica se o usuário pode marcar uma multa como paga.
     *
     * @param \Authorization\IdentityInterface $user O usuário logado.
     * @param \App\Model\Entity\PlayerCycleSummary $playerCycleSummary A entidade do resumo.
     * @return bool
     */
    public function canMarkFinePaid(IdentityInterface $user, PlayerCycleSummary $playerCycleSummary)
    {
        // Apenas administradores podem marcar multas como pagas.
        return $user->get('role') === 'admin';
    }

    // Você pode adicionar aqui policies para `canAdd`, `canEdit`, `canDelete` se precisar dessas actions
    // para PlayerCycleSummary e quiser protegê-las.
    // Exemplo:
    // public function canEdit(IdentityInterface $user, PlayerCycleSummary $playerCycleSummary)
    // {
    //     return $user->get('role') === 'admin';
    // }
} 