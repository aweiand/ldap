<?php

//definições padrão para conexão no sistema

ldap_set_option(NULL, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option(NULL, LDAP_OPT_REFERRALS,0);

$server = "localhost";
$dn = "dc=empresa,dc=com,dc=br";

if($ds = ldap_connect($server))
{ 
    print "LDAP connected to:".$server."<br />"; 
}
else {
    die("Could not connect to LDAP server.");
}

$uname = "cn=Manager,".$dn;
$pw = "tux";

$passwd = "{MD5}".base64_encode(pack("H*",md5($_POST["senha"])));
$usuario = $_POST["usuario"];


//função para consulta de usuario na base LDAP

if($ds){
    if (ldap_bind($ds, $uname, $pw)){
        echo "logado no LDAP como usuario: ".$uname."<br />";

        $filter='(&(uid='.$usuario.')(userpassword='.$passwd.'))';
        
        /* Este atributo serve para filtro dos resultados no ldap_serach */
        // $justthese = array("ou", "sn", "givenname", "mail", "userPassword");

        $sr=ldap_search($ds, $dn, $filter);

        $info = ldap_get_entries($ds, $sr);

        echo $info["count"]." entries returned<br />";
        
 //       echo var_dump($info);
     if ($info["count"]!=0) {
            echo "<br /><strong>Logado...!!!! :D</strong><br />";
            echo $info[0]["userpassword"][0]."<br />";
            echo $info[0]["dn"]."<br />";
            echo $info[0]["sn"][0]."<br />";
            echo $info[0]["uid"][0]."<br />";
            echo $info[0]["givenname"][0]."<br />";
            echo "E-mail: ".$info[0]["mail"][0]."<br />";
            echo "Permissão: ".$info[0]["permis"][0]."<br />";
            echo "AppId: ".$info[0]["appid"][0]."<br />";
     }
        
    }
}

?>

