<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Table\PlayerCycleSummariesTable;
use Authorization\IdentityInterface;

class PlayerCycleSummariesTablePolicy
{
    /**
     * Verifica se o usuário pode acessar a action index (listar resumos).
     *
     * @param \Authorization\IdentityInterface $user O usuário logado.
     * @param \App\Model\Table\PlayerCycleSummariesTable $table A tabela.
     * @return bool
     */
    public function canIndex(IdentityInterface $user, PlayerCycleSummariesTable $table)
    {
        // Permite que qualquer usuário logado acesse o index.
        // Se você não tiver usuários logados, esta verificação falhará.
        // Considere pular a autorização para esta action no controller se for pública.
        return true; // Ou return $user !== null; se só usuários logados
    }

    /**
     * Verifica se o usuário pode processar os resumos de ciclo.
     *
     * @param \Authorization\IdentityInterface $user O usuário logado.
     * @param \App\Model\Table\PlayerCycleSummariesTable $table A tabela.
     * @return bool
     */
    public function canProcessCycleSummaries(IdentityInterface $user, PlayerCycleSummariesTable $table)
    {
        // Apenas administradores podem processar resumos.
        // Assumindo que seu objeto IdentityInterface tem um método get() ou uma propriedade para 'role'.
        return $user->get('role') === 'admin';
    }
} 