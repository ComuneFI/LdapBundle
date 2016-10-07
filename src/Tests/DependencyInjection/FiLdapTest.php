<?php

namespace Tests\LdapBundle;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Fi\LdapBundle\DependencyInjection\FiLdap;

class FiLdapTest extends KernelTestCase
{
    private $container;

    public function setUp()
    {
        self::bootKernel();

        $this->container = self::$kernel->getContainer();
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function testLdap()
    {
        $ldapParms = $this->getLdapParms();
        $ldap = new FiLdap($ldapParms);
        $ldap->connect();

        $users = $ldap->getUtenti();

        // assert that your calculator added the numbers correctly!
        $this->assertGreaterThanOrEqual(0, $users);
    }

    private function getLdapParms()
    {
        $parms = array();
        $ldapparms = $this->getContainer()->getParameter('connessione_ldap');
        $parms['host'] = $ldapparms['host'];
        $parms['port'] = $ldapparms['port'];
        $parms['username'] = $ldapparms['username'];
        $parms['password'] = $ldapparms['password'];
        $parms['basedn'] = $ldapparms['basedn'];
        $parms['userfilter'] = '(&(objectClass=user)(!(cn=u*))(!(cn=*x))(!(cn=U*))(!(cn=T*))(!(cn=t*))(!(cn=c*))(!(cn=C*))(!(cn=i*))(!(cn=I*))(!(cn=a*))(!(cn=A*)))';
        $parms['attribute'] = $ldapparms['attribute'];

        return $parms;
    }
}
