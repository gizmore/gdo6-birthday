<?php
namespace GDO\Birthday;

use GDO\Core\GDO_Module;
use GDO\DB\GDT_Checkbox;
use GDO\Friends\GDT_ACL;

/**
 * Birthday module.
 * @author gizmore
 * @version 6.10.1
 * @since 6.10.1
 */
final class Module_Birthday extends GDO_Module
{
    public function getDependencies() { return ['Profile', 'Friends']; }
    
    public function getConfig()
    {
        return [
            GDT_Checkbox::make('birthday_alerts')->initial('1'),
        ];
    }
    
    public function getUserSettings()
    {
        return [
            GDT_Birthdate::make('birthday'),
            GDT_ACL::make('birthday_visible')->initial('acl_noone'),
            GDT_Checkbox::make('announce_my_birthday'),
        ];
    }
    
    /**
     * On init, display other people birthdates.
     * @todo implement.
     */
    public function onInit()
    {
        
    }
    
}
