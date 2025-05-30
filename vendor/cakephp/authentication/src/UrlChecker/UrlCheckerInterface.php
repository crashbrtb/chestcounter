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
namespace Authentication\UrlChecker;

use Psr\Http\Message\ServerRequestInterface;

/**
 * UrlCheckerInterface
 */
interface UrlCheckerInterface
{
    /**
     * Checks the requests if it is the configured login action
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Server Request
     * @param array|string $loginUrls Login URL string or array of URLs
     * @param array<string, mixed> $options Array of options
     * @return bool
     */
    public function check(ServerRequestInterface $request, array|string $loginUrls, array $options = []): bool;
}
