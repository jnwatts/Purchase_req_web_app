<?php
namespace PurchaseReqs;
if (!defined('PURCHASE_REQS')) { die('Oops!'); }

class Ldap {
    private $config;

    public function __construct($ci) {
        $this->config = $ci->config['ldap'];
    }

    public function getUser($username) {
        $config = $this->config;

        preg_match(
                '/(?<scheme>[a-z]+?):\/\/' // scheme
                .'(?<host>[a-z0-9\-\._]+)' // host
                .'(?::(?<port>[0-9]+))?' // port
                .'\/?(?<dn>[a-z0-9,=]*)' // dn
                .'(?:\?|$)?/si',
                $config['ldap_url'], $m);
        $ldap_host = $m['scheme'].'://'.$m['host'];
        if (isset($m['port']) && $m['port'] != NULL) {
            $ldap_host .= ':'.$m['port'];
        }
        $h = ldap_connect($ldap_host); //or die('Could not connect to LDAP');
        if (!$h)
            return NULL;
        $b = ldap_bind($h, $config['bind_dn'], $config['bind_pw']);
        if (!$b) {
            ldap_close($h);
            return NULL;
        }

        $param_map = $config['user_params'];
        $params = [];
        foreach ($param_map as $k => $v) {
            $params[] = $v;
        }

        $results = ldap_search($h, $config['base_dn'], '('.$param_map['username'].'='.$username.')', $params);
        $entries = ldap_get_entries($h, $results);

        ldap_close($h);

        if ($entries['count'] == 0) {
            return NULL; //die('No entries');
        }

        foreach ($param_map as $k => $v) {
            if (isset($entries[0][$v])) {
                if ($entries[0][$v]['count'] == 1) {
                    $result[$k] = $entries[0][$v][0];
                } else {
                    foreach ($entries[0][$v] as $i => $e) {
                        if ($i == 'count')
                            continue;
                        $result[$k][] = $e;
                    }
                }
            }
        }

        return $result;
    }
}
