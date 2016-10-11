<?php

namespace Fi\LdapBundle\DependencyInjection;

class FiLdapUtils
{
    public static function getFormatTime($time)
    {
        $winSecs = (int) ($time / 10000000); // divide by 10 000 000 to get seconds
        $unixTimestamp = ($winSecs - 11644473600); // 1.1.1600 -> 1.1.1970 difference in seconds
        return date(\DateTime::RFC822, $unixTimestamp);
    }

    public static function getLdapHostParam($parms)
    {
        return (isset($parms['host'])) ? $parms['host'] : '';
    }

    public static function getLdapPortParam($parms)
    {
        return (isset($parms['port'])) ? $parms['port'] : '';
    }

    public static function getLdapUsernameParam($parms)
    {
        return (isset($parms['username'])) ? $parms['username'] : '';
    }

    public static function getLdapPasswordParam($parms)
    {
        return (isset($parms['password'])) ? $parms['password'] : '';
    }

    public static function getLdapBasednParam($parms)
    {
        return (isset($parms['basedn'])) ? $parms['basedn'] : '';
    }

    public static function getLdapUserFilterParam($parms)
    {
        return (isset($parms['userfilter'])) ? $parms['userfilter'] : '';
    }

    public static function getLdapAttributeParam($parms)
    {
        return (isset($parms['attribute'])) ? $parms['attribute'] : array();
    }

    public static function getLdapBasednUtenti($parms)
    {
        return (isset($parms['base_dn'])) ? $parms['base_dn'] : $this->basedn;
    }

    public static function getLdapUserFilterUtenti($parms)
    {
        return (isset($parms['filter'])) ? $parms['filter'] : $this->filterutenti;
    }

    public static function getLdapAttributeUtenti($parms)
    {
        return (isset($parms['attribute']) && is_array($parms['attribute'])) ? $parms['attribute'] : $this->attributi;
    }

    public static function getLdapUsernameUtenti($parms)
    {
        return self::getLdapUsernameParam($parms);
    }
}
