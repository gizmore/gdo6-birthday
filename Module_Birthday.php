<?php
namespace GDO\Birthday;

use GDO\Core\GDO_Module;
use GDO\DB\GDT_Checkbox;
use GDO\Friends\GDT_ACL;
use GDO\DB\GDT_UInt;
use GDO\User\GDO_User;
use GDO\Date\Time;
use GDO\Session\GDO_Session;
use GDO\Core\GDT_Response;

/**
 * Birthday module.
 * - Birthday alerts
 * - Age verification for methods and global.
 * 
 * @author gizmore
 * @version 6.10.4
 * @since 6.10.1
 */
final class Module_Birthday extends GDO_Module
{
    public function getDependencies() { return ['Profile', 'Friends']; }
    
    public function onLoadLanguage() { return $this->loadLanguage('lang/birthday'); }
    
    public function getConfig()
    {
        return [
            GDT_Checkbox::make('birthday_alerts')->initial('1'),
            GDT_UInt::make('global_min_age')->bytes(1)->unsigned()->initial('0'),
            GDT_UInt::make('method_min_age')->bytes(1)->unsigned()->initial('21'),
        ];
    }
    
    public function cfgBirthdayAlerts() { return $this->getConfigVar('birthday_alerts'); }
    public function cfgGlobalMinAge() { return $this->getConfigVar('global_min_age'); }
    public function cfgMethodMinAge() { return $this->getConfigVar('method_min_age'); }
    
    public function getUserSettings()
    {
        return [
            GDT_Birthdate::make('birthday'),
            GDT_ACL::make('age_visible')->initial('acl_noone'),
            GDT_ACL::make('birthdate_visible')->initial('acl_noone'),
            GDT_Checkbox::make('announce_my_birthday'),
        ];
    }
    
    /**
     * On init, display other people birthdates.
     * @TODO implement.
     */
    public function onInit()
    {
    }
    
    public function onIncludeScripts()
    {
        $this->addCSS('css/birthday.css');
    }
    
    ####################
    ### Agecheck API ###
    ####################
    public function agecheckDisplay($minAge)
    {
        return GDT_Response::makeWith(
            GDT_AgeCheck::make()->
            minAge($minAge)->errorMinAge())
        ->code(403);
    }
    
    public function agecheckIsMethodExcepted()
    {
        $mome = mo() . '::' . me();
        $exceptions = [
            'Language::GetTransData',
            'Birthday::VerifyAge',
        ];
        return in_array($mome, $exceptions, true);
    }
    
    public function agecheckGlobal($minAge)
    {
        $user = GDO_User::current();
        $age = $this->getUserAge($user);
        return $age >= $minAge;
    }
    
    public function getUserAge(GDO_User $user)
    {
        if (!($birthdate = $this->userSettingVar($user, 'birthday')))
        {
            if (!($birthdate = $this->getUserAgeSession($user)))
            {
                return null;
            }
        }
        return Time::getAge($birthdate);
    }
    
    private function getUserAgeSession(GDO_User $user)
    {
        if (class_exists('GDO\Session\GDO_Session', false))
        {
            return GDO_Session::get('birthdate');
        }
    }
    
    #############
    ### Hooks ###
    #############
    public function hookBeforeExecute()
    {
        $user = GDO_User::current();
        if (!$user->isStaff())
        {
            if ($minAge = $this->cfgGlobalMinAge())
            {
                if (!$this->agecheckIsMethodExcepted())
                {
                    if (!$this->agecheckGlobal($minAge))
                    {
                        return $this->agecheckDisplay($minAge);
                    }
                }
            }
        }
    }
    
}
