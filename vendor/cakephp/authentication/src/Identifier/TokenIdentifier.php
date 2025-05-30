<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Authentication\Identifier;

use ArrayAccess;
use Authentication\Identifier\Resolver\ResolverAwareTrait;
use Cake\Utility\Security;

/**
 * Token Identifier
 */
class TokenIdentifier extends AbstractIdentifier
{
    use ResolverAwareTrait;

    public const CREDENTIAL_TOKEN = 'token';

    /**
     * Default configuration.
     *
     * @var array
     */
    protected array $_defaultConfig = [
        'tokenField' => 'token',
        'dataField' => self::CREDENTIAL_TOKEN,
        'resolver' => 'Authentication.Orm',
        'hashAlgorithm' => null,
    ];

    /**
     * @inheritDoc
     */
    public function identify(array $credentials): ArrayAccess|array|null
    {
        $dataField = $this->getConfig('dataField');
        if (!isset($credentials[$dataField])) {
            return null;
        }

        if ($this->getConfig('hashAlgorithm') !== null) {
            $credentials[$dataField] = Security::hash(
                $credentials[$dataField],
                $this->getConfig('hashAlgorithm'),
            );
        }

        $conditions = [
            $this->getConfig('tokenField') => $credentials[$dataField],
        ];

        return $this->getResolver()->find($conditions);
    }
}
