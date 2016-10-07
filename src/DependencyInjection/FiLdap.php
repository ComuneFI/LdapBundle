<?php

namespace Fi\LdapBundle\DependencyInjection;

class FiLdap
{
    private $ldapHost;
    private $ldapPort;
    private $ldapUser;
    private $ldapPwd;
    private $basedn;
    private $filterutenti;
    private $attributi;
    private $bind;
    private $connection;

    public function __construct($parms)
    {
        $this->ldapHost = (isset($parms['host'])) ? $parms['host'] : '';
        $this->ldapPort = (isset($parms['port'])) ? $parms['port'] : '';
        $this->ldapUser = (isset($parms['username'])) ? $parms['username'] : '';
        $this->ldapPwd = (isset($parms['password'])) ? $parms['password'] : '';
        $this->basedn = (isset($parms['basedn'])) ? $parms['basedn'] : '';
        $this->filterutenti = (isset($parms['userfilter'])) ? $parms['userfilter'] : '';
        $this->attributi = (isset($parms['attribute'])) ? $parms['attribute'] : array();

        $this->connect();
    }

    public function getUtenti($parms = array())
    {
        $base_dn = (isset($parms['base_dn'])) ? $parms['base_dn'] : $this->basedn;
        $filter = (isset($parms['filter'])) ? $parms['filter'] : $this->filterutenti;
        $attributi = (isset($parms['attribute']) && is_array($parms['attribute'])) ? $parms['attribute'] : $this->attributi;

        $read = ldap_search($this->connection, $base_dn, $filter, $attributi)
                or exit('>>Unable to search ldap server<<');
        $users = array();
        $info = ldap_get_entries($this->connection, $read);

        //Per visualizzare tutti i dettagli in Active Directory
        //$ii = 0;
        //for ($i = 0; $ii < $info[$i]["count"]; $ii++) {
        //    $data = $info[$i][$ii];
        //    echo $data . ":&nbsp;&nbsp;" . $info[$i][$data][0] . "<br>";
        //}
        //exit;

        for ($i = 0; $i < $info['count']; ++$i) {
            $user = array();
            foreach ($attributi as $attributo) {
                $user = array_merge($user, array($attributo => $this->getValue($info, $attributo, $i)));
            }
            $users[] = $user;
        }

        return $users;
    }

    public function getUserInformation($parms = array())
    {
        $base_dn = (isset($parms['base_dn'])) ? $parms['base_dn'] : $this->basedn;
        $username = (isset($parms['username'])) ? $parms['username'] : '';

        if (strlen($username) > 0) {
            $filter = "(&(objectClass=user)(cn=$username))";
        } else {
            $filter = (isset($parms['filter'])) ? $parms['filter'] : $this->filterutenti;
        }

        $read = ldap_search($this->connection, $base_dn, $filter)
                or exit('>>Unable to search ldap server<<');
        $info = ldap_get_entries($this->connection, $read);

        //Per visualizzare tutti i dettagli in Active Directory
        $ii = 0;
        $userinfo = array();
        if (isset($info[0]['count'])) {
            for ($i = 0; $ii < $info[$i]['count']; ++$ii) {
                $data = $info[$i][$ii];
                $userinfo = array_merge($userinfo, array($data => $this->getValue($info, $info[$i][$ii], $i)));
            }
        }

        return $userinfo;
    }

    public function connect()
    {
        try {
            $this->connection = ldap_connect($this->ldapHost, $this->ldapPort);
            ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);
            $this->bind = ldap_bind($this->connection, $this->ldapUser, $this->ldapPwd);
        } catch (Exception $exc) {
            return array('errcode' => -1, 'message' => 'Impossibile connettersi al server LDAP: '.$exc->getTraceAsString());
        }

        return array('errcode' => 0, 'message' => 'Connesso al server LDAP');
    }

    public function disconnect()
    {
        try {
            ldap_close($this->connection);
        } catch (Exception $exc) {
            return array('errcode' => -1, 'message' => 'Impossibile disconnettersi dal server LDAP: '.$exc->getTraceAsString());
        }

        return array('errcode' => 0, 'message' => 'Disonnesso dal server LDAP');
    }

    private function getValue($info, $ldapattribute, $index)
    {
        return isset($info[$index][$ldapattribute][0]) ? $info[$index][$ldapattribute][0] : '';
    }

    public function cercaUtente($arrayUtenti, $search_text)
    {
        //Funzione utile per cercare utenti estraendo una sola volta da ldap
        //e cercando nell'array invece che via ldap
        set_time_limit(0);
        $matches = array();
        $pattern = "/$search_text\z/i";  //contains an string
        //loop through the data
        foreach ($arrayUtenti as $key => $value) {
            //loop through each key under data sub array
            foreach ($value as $key2 => $value2) {
                if ($key2 === 'cn') {
                    //check for match.
                    if (preg_match($pattern, $value2)) {
                        //add to matches array.
                        //$matches[$key] = $value;
                        $matches[] = $value;
                        //match found, so break from foreach
                        break;
                    }
                }
            }
        }

        return $matches;
    }

    public function dumpUtenti($parms = array())
    {
        $base_dn = (isset($parms['base_dn'])) ? $parms['base_dn'] : $this->basedn;
        $filter = (isset($parms['filter'])) ? $parms['filter'] : $this->filterutenti;
        $attributi = (isset($parms['attribute']) && is_array($parms['attribute'])) ? $parms['attribute'] : $this->attributi;
        if (count($attributi) === 0) {
            $read = ldap_search($this->connection, $base_dn, $filter)
                    or exit('>>Unable to search ldap server<<');
        } else {
            $read = ldap_search($this->connection, $base_dn, $filter, $attributi)
                    or exit('>>Unable to search ldap server<<');
        }

        $info = ldap_get_entries($this->connection, $read);

        //Per visualizzare tutti i dettagli in Active Directory
        echo $info['count'].' entrees retournees<br/><br/>';
        for ($ligne = 0; $ligne < $info['count']; ++$ligne) {
            for ($colonne = 0; $colonne < $info[$ligne]['count']; ++$colonne) {
                $data = $info[$ligne][$colonne];
                switch ($data) {
                    case 'accountexpires':
                        echo $data.':'.$this->getFormatTime($info[$ligne][$data][0]).'<br/>';
                        break;

                    case 'thumbnailphoto':
                        $imageString = $info[$ligne][$data][0];
                        $tempFile = tempnam(sys_get_temp_dir(), 'image');
                        file_put_contents($tempFile, $imageString);
                        $finfo = new \finfo(FILEINFO_MIME_TYPE);
                        $mime = explode(';', $finfo->file($tempFile));
                        echo '<img src="data:'.$mime[0].';base64,'.base64_encode($imageString).'"/>';
                        break;

                    default:
                        echo $data.':'.$info[$ligne][$data][0].'<br/>';
                        break;
                }
            }
            echo '<br/>';
        }
        exit;
    }

    private function getFormatTime($time)
    {
        $winSecs = (int) ($time / 10000000); // divide by 10 000 000 to get seconds
        $unixTimestamp = ($winSecs - 11644473600); // 1.1.1600 -> 1.1.1970 difference in seconds
        return date(\DateTime::RFC822, $unixTimestamp);
    }
}
