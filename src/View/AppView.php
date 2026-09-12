<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.0.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\View;

use Cake\View\View;
use CakeLte\View\CakeLteTrait;

/**
 * Application View
 *
 * Your application's default view class
 *
 * @link https://book.cakephp.org/4/en/views.html#the-app-view
 */
class AppView extends View
{
    use CakeLteTrait;

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like adding helpers.
     *
     * e.g. `$this->addHelper('Html');`
     *
     * @return void
     */
    public string $layout = 'CakeLte/layout/default';
    public function initialize(): void
    {
        parent::initialize();
        $this->addHelper('CakeLte.CakeLte');
        // Branding is in the <head> and the navbar of every layout, so the
        // helper has to be here rather than added per-controller.
        $this->addHelper('Branding');
        // The theme goes on the <html> element of every layout.
        $this->addHelper('SiteTheme');
        
        // Inicializa o CakeLte com configurações específicas

    }
}
