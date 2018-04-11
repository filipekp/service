<?php
  namespace prosys\core\common;

  /**
   * Represents identity map, which keeps every agent object.<br />
   * Means e.g.: data access objects, views, SQL connection, ...
   * 
   * @author Jan Svěží
   * @copyright (c) 2014, Proclient s.r.o.
   */
  class Agents
  {
    const TYPE_COMMON = '';
    const TYPE_MODEL = 'model';
    const TYPE_VIEW_FRONTEND = 'fe_view';
    const TYPE_VIEW_ADMIN = 'admin_view';
    const TYPE_CONTROLLER_FRONTEND = 'fe_controller';
    const TYPE_CONTROLLER_ADMIN = 'admin_controller';
    const TYPE_SECURITY = 'security';
    const TYPE_TYPES = 'types';
    
    private static $NS = array(
      ''                 => '\prosys\core\common\\',
      'model'            => '\prosys\model\\',
      'admin_view'       => '\prosys\admin\view\\',
      'admin_controller' => '\prosys\admin\controller\\',
      'fe_view'          => '\prosys\web\view\\',
      'fe_controller'    => '\prosys\web\controller\\',
      'security'         => '\prosys\admin\security\\',
      'types'            => '\prosys\core\common\types\\'
    );

    private static $AGENTS = array();
    
    /**
     * Returns namespace of type.
     * 
     * @param string $type Agents::TYPE_{type}
     * @return string
     */
    public static function getNamespace($type) {
      return self::$NS[$type];
    }
    
    /**
     * Vrati agenta.
     * 
     * @param string $agent
     * @param string $type Agents::TYPE_{type}
     * @param array|NULL $params
     * @param string $tag vlastni oznaceni / klic agenta. Pod timto klicem bude mozne agenta vyzvednout
     * 
     * @return mixed
     */
    public static function getAgent($agent, $type = '', $params = NULL, $tag = '') {
      $agent = self::$NS[$type] . $agent;
      $tag = (($tag) ? $tag : $agent);
      
      if (!array_key_exists($tag, self::$AGENTS)) {
        try {
          $r = new \ReflectionClass($agent);
          
          if (is_null($params)) {
            self::$AGENTS[$tag] = $r->newInstance();
          } else {
            self::$AGENTS[$tag] = $r->newInstanceArgs($params);
          }
        } catch (\ReflectionException $e) {
          \PC::debug($e, 'reflection exception');

          return FALSE;
        }
      }
      
      return self::$AGENTS[$tag];
    }
    
    /**
     * Vrati agenta oznaceneho vlastnim tagem.
     * 
     * @param string $tag
     * @return mixed|NULL
     */
    public static function getAgentByTag($tag) {
      return Functions::item(self::$AGENTS, $tag);
    }
  }
