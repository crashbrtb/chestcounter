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
        // Assumindo que a identidade do usuário (IdentityInterface) já tem os roles carregados
        // como uma coleção de entidades Role, e cada entidade Role tem uma propriedade 'name'.
        $userAssociatedRoles = $user->get('roles'); // 'roles' deve ser o nome da propriedade na entidade User

        if (!empty($userAssociatedRoles) && (is_array($userAssociatedRoles) || $userAssociatedRoles instanceof \Traversable)) {
            foreach ($userAssociatedRoles as $roleEntity) {
                if (is_object($roleEntity) && isset($roleEntity->name) && $roleEntity->name === 'admin') {
                    return true; // Encontrou o role admin
                }
            }
        }
        return false; // Não é admin ou não tem roles
    }
} 