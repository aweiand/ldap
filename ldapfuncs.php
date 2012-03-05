<?php
require('adodb/adodb.inc.php');

class LDAP{
    var $host;
    var $ldapbase;
    var $uname;
    var $pw;

    function LDAP(){
        $LDAP_CONNECT_OPTIONS = Array(
            Array ("OPTION_NAME"=>LDAP_OPT_DEREF, "OPTION_VALUE"=>2),
            Array ("OPTION_NAME"=>LDAP_OPT_SIZELIMIT,"OPTION_VALUE"=>100),
            Array ("OPTION_NAME"=>LDAP_OPT_TIMELIMIT,"OPTION_VALUE"=>30),
            Array ("OPTION_NAME"=>LDAP_OPT_PROTOCOL_VERSION,"OPTION_VALUE"=>3),
            Array ("OPTION_NAME"=>LDAP_OPT_ERROR_NUMBER,"OPTION_VALUE"=>13),
            Array ("OPTION_NAME"=>LDAP_OPT_REFERRALS,"OPTION_VALUE"=>FALSE),
            Array ("OPTION_NAME"=>LDAP_OPT_RESTART,"OPTION_VALUE"=>FALSE)
        );
        $this->host     = '127.0.0.1';
        $this->ldapbase = 'dc=empresa,dc=com,dc=br';
        $this->uname    = "cn=usuarioMaster,".$this->ldapbase;
        $this->pw       = "SenhaMaster";
    }    
    
    function _incPessoa($arrDados){
        $ds = ldap_connect($this->host);
        ldap_bind($ds, $this->uname, $this->pw);
        
        if (ldap_add($ds, "uid=".$arrDados['uid'].",ou=Pessoas,".$this->ldapbase, $arrDados))
                return true;
        else
                return false;
    }
    
    function _altPessoa($arrDados){
        $ds = ldap_connect($this->host);
        ldap_bind($ds, $this->uname, $this->pw);
        
        if (ldap_modify($ds, "cn=".$arrDados['cn'].",ou=Pessoas,dc=empresa,dc=com,dc=br", $arrDados ))
                return true;
        else
                return false;
        
    }    
    
    function _login($user,$passw){
        $this->LDAP();
        $ldap = NewADOConnection( 'ldap' );
        $ldap->Connect( $this->host, $this->uname, $this->pw, $this->ldapbase );
        $ldap->SetFetchMode(ADODB_FETCH_ASSOC);
   
        $passwd = "{SHA}".base64_encode(pack("H*",sha1($passw)));
        $filter="(&(uid=".$user.")(userpassword=".$passwd."))";
        
        $rs = $ldap->Execute( $filter );
        
        if ($rs){
            if ($rs->RecordCount()!=0){
                $this->codusuario = $rs->Fields("uidNumber");
                
                $filter = "(&(member=uid=".$user.",ou=Pessoas,".$this->ldapbase."))";
                $rs = $ldap->Execute($filter);            
                
                if ($rs)
                if ($rs->RecordCount()!=0){
                    if ($rs->Fields("cn")!=null){
                        $this->grupo    = $rs->Fields("cn");
                        $this->valido = true;
                    }
                } else {
                    $this->valido = false;
                }
            } else {
                $this->valido = false;
            }
        }
    }
    
    function _incGrupo($arrDados){
        $ds = ldap_connect($this->host);
        ldap_bind($ds, $this->uname, $this->pw);
        
        if (ldap_add($ds, "cn=".$arrDados['cn'].",ou=Grupos,".$this->ldapbase, $arrDados))
                return true;
        else
                return false;
    }
    
    function _altGrupo($arrDados){
        $ds = ldap_connect($this->host);
        ldap_bind($ds, $this->uname, $this->pw);
        
        if (ldap_modify($ds, "cn=".$arrDados['cn'].",ou=Grupos,dc=empresa,dc=com,dc=br", $arrDados ))
                return true;
        else
                return false;
    }
    
    function _incPessoaGrupo($arrDados,$grupo){
        $ds = ldap_connect($this->host);
        ldap_bind($ds, $this->uname, $this->pw);

        if (ldap_mod_add($ds, "cn=".$grupo.",ou=Grupos,dc=empresa,dc=com,dc=br", $arrDados ))
                return true;
        else
                return false;
    }        
    
    function _delPessoaGrupo($arrDados,$grupo){
        $ds = ldap_connect($this->host);
        ldap_bind($ds, $this->uname, $this->pw);

        if (ldap_mod_del($ds, "cn=".$grupo.",ou=Grupos,dc=empresa,dc=com,dc=br", $arrDados ))
                return true;
        else
                return false;
    }      
    
    function _del($dn){
        $ds = ldap_connect($this->host);
        ldap_bind($ds, $this->uname, $this->pw);
        
        if (ldap_delete($ds, $dn.",".$this->ldapbase))
            return true;
        else
            return false;
    }
    
    function _lst($filter = '', $don = ''){
        $this->LDAP();
        $ldap = NewADOConnection( 'ldap' );
        $ldap->Connect( $this->host, $this->uname, $this->pw, $don.$this->ldapbase );
        $ldap->SetFetchMode(ADODB_FETCH_ASSOC);
        //$ldap->debug=true;
        if ($filter=='')
            $filter = "(&(uid=*))";
        $rs = $ldap->Execute($filter);
        return $rs;
    }
    
}


//incluindo grupo
if (isset($_POST['incG'])){
    $arrDados["cn"]         = $_POST['cn'];
    $arrDados["appId"]      = $_POST['appId'];
    $arrDados["permis"]     = $_POST['permis'];
    $arrDados["member"]     = "cn=usuarioMaster,dc=empresa,dc=com,dc=br";
    
    $arrDados["objectClass"][0] = "groupOfNames";
    $arrDados["objectClass"][1] = "top";

    $ldap = new LDAP;
    if ($ldap->_incGrupo($arrDados))
        echo "Obaaaa";
    else
        echo "aff...";
}

//incluindo usuario
if (isset($_POST['incU'])){
    $arrDados["uid"] = $_POST['login'];
    $arrDados["cn"]  = $_POST['cn'];
    $arrDados["sn"]  = $_POST['sn'];
    $arrDados["givenName"]  = $_POST['givenname'];
    $arrDados["mail"] = $_POST['email'];
    $arrDados["gidNumber"] = "100000";
    $arrDados["uidNumber"] = $_POST['uid'];
    $arrDados["homeDirectory"] = "/usr/publico";
    $arrDados["userpassword"] = $_POST['senha'];
    
    $arrDados["objectclass"][0] = "person";
    $arrDados["objectclass"][1] = "inetOrgPerson";
    $arrDados["objectclass"][2] = "posixAccount";

    $ldap = new LDAP;
    if ($ldap->_incPessoa($arrDados))
        echo "Obaaaa";
    else
        echo "aff...";
}

//inserindo pessoas no grupo
if (isset($_POST['incNG'])) {
    $ldap = new LDAP();
    $grupo = $_POST['grp'];

    $rs  = $ldap->_lst("(&(uid=".$_POST['usr']."))");
    if ($rs->RowCount())
        $arrDados["member"] = "uid=".$rs->Fields("uid").",ou=Pessoas,dc=empresa,dc=com,dc=br";
        
    if ($ldap->_incPessoaGrupo($arrDados,$grupo)){
        echo "<br /><strong>Mas aaaaaaaaa</strong><br />";
    } else {
        echo "<br />se você está vendo esta mensagem nao se preocupe nossos macacos altamente treinados já estão resolvendo seu problema.....";
    }
}


//deletando pessoas do grupo
if (isset($_POST['delUG'])){
    $ldap = new LDAP();
    $grupo = $_POST['grpd'];

    $rs  = $ldap->_lst("(&(uid=".$_POST['usrd']."))");
    if ($rs->RowCount())
        $arrDados["member"] = "uid=".$rs->Fields("uid").",ou=Pessoas,dc=empresa,dc=com,dc=br";
        
    if ($ldap->_delPessoaGrupo($arrDados,$grupo)){
        echo "<br /><strong>Mas aaaaaaaaa</strong><br />";
    } else {
        echo "<br />se você está vendo esta mensagem nao se preocupe nossos macacos altamente treinados já estão resolvendo seu problema.....";
    }
}

//alterando dados do grupo
if (isset($_POST['altGrupoDados'])){
    $ldap = new LDAP();
    $arrDados["cn"]     = $_POST['cn'];
    $arrDados["appId"]  = $_POST['appId'];
    $arrDados["permis"] = $_POST['permis'];
    
    if ($ldap->_altGrupo($arrDados)){
        echo "<br /><strong>Mas aaaaaaaaa</strong><br />";
    } else {
        echo "<br />se você está vendo esta mensagem nao se preocupe nossos macacos altamente treinados já estão resolvendo seu problema.....";
    }
}

//alterando dados do grupo
if (isset($_POST['delGrupo'])){
    $ldap = new LDAP();
    $end   = "cn=".$_POST['delGrupo'].",ou=Grupos";
    
    if ($ldap->_del($end)){
        echo "<br /><strong>Mas aaaaaaaaa</strong><br />";
    } else {
        echo "<br />se você está vendo esta mensagem nao se preocupe nossos macacos altamente treinados já estão resolvendo seu problema.....";
    }
}

?>

<form method="POST">
    <p>Cadastro de Grupo no LDAP</p>
    <label for="cn">Nome</label>
    <input type="text" name="cn" />
    <br />
    <input type="hidden" name="incG" />
    <input type="submit" />
</form>

---------------------------------------------------

<form method="POST">
    <p>Cadastro de Usuário no LDAP</p>
    <label for="login">Login</label>
    <input type="text" name="login" />
    <br />
    <label for="senha">Senha</label>           
    <input type="password" name="senha" />
    <br />
    <label for="cn">Nome Visualizacao</label>
    <input type="text" name="cn" />
    <br />
    <label for="sn">Sobrenome</label>
    <input type="text" name="sn" />
    <br />
    <label for="givenname">Nome</label>
    <input type="text" name="givenname" />
    <br />
    <label for="email">E-Mail</label>
    <input type="text" name="email" />
    <br />
    <label for="uid">UserId</label>
    <input type="text" name="uid" />
    
    <input type="hidden" name="incU" />
    <input type="submit" />
</form>

-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#

<form method="POST">
    <p>Alteração de Usuário no LDAP</p>
    <label for="buid">Login</label>
    <input type="text" name="buid" />
    <br />
    <input type="submit" />
</form>

<?php
if (isset($_POST["buid"])){
    $ldap = new LDAP();
    $rs = $ldap->_lst("(&(uid=".$_POST['buid']."))");    
?>

<form method="POST">
    <p>Alteração de Usuário no LDAP</p>
    <label for="login">Login</label>
    <input type="text" name="login" value="<?php echo $rs->Fields('uid'); ?>" />
    <br />
    <label for="senha">Senha</label>           
    <input type="password" name="senha" value="<?php echo $rs->Fields('userPassword'); ?>" />
    <br />
    <label for="cn">Nome Visualizacao</label>
    <input type="text" name="cn" value="<?php echo $rs->Fields('cn'); ?>" />
    <br />
    <label for="sn">Sobrenome</label>
    <input type="text" name="sn" value="<?php echo $rs->Fields('sn'); ?>" />
    <br />
    <label for="givenname">Nome</label>
    <input type="text" name="givenname" value="<?php echo $rs->Fields('givenName'); ?>" />
    <br />
    <label for="email">E-Mail</label>
    <input type="text" name="email" value="<?php echo $rs->Fields('mail'); ?>" />
    <br />
    <label for="uid">UserId</label>
    <input type="text" name="uid"value="<?php echo $rs->Fields('uidNumber'); ?>" />
    
    <input type="hidden" name="altU" />
    <input type="submit" />
</form>

<?php 
}
?>


-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#

<form method="POST">
    <p>Inserindo Usuário no Grupo LDAP</p>
    <label for="grupos">Grupo</label>
    <?php 
    $l  = new LDAP();
    $rs = $l->_lst("(|(cn=*))","ou=Grupos,");
    $u  = new LDAP();
    $ru = $u->_lst("(|(cn=*))");
    ?>
    <select name="grp">
        <?php 
            while (!$rs->EOF)
            {
                echo "<option value=".$rs->Fields('cn').">".$rs->Fields('cn')."</option>";
                $rs->MoveNext();
            }
        ?>
    </select>
    <br />
    <label for="usr">Usuários</label>
    <select name="usr">
        <?php 
            while (!$ru->EOF)
            {
                echo "<option value=".$ru->Fields('uid').">".$ru->Fields('uid')."</option>";
                $ru->MoveNext();
            }
        ?>
    </select>
    
    <input type="hidden" name="incNG" />
    <input type="submit" />
</form>

---------------------------------------------------

<form method="POST">
    <p>Excluindo Usuário do Grupo LDAP</p>
    <label for="grpd">Grupo</label>
    <?php 
    $l  = new LDAP();
    $rs = $l->_lst("(|(cn=*))","ou=Grupos,");
    $u  = new LDAP();
    $ru = $u->_lst("(|(cn=*))");
    ?>
    <select name="grpd">
        <?php 
            while (!$rs->EOF)
            {
                echo "<option value=".$rs->Fields('cn').">".$rs->Fields('cn')."</option>";
                $rs->MoveNext();
            }
        ?>
    </select>
    <br />
    <label for="usrd">Usuários</label>
    <select name="usrd">
        <?php 
            while (!$ru->EOF)
            {
                echo "<option value=".$ru->Fields('uid').">".$ru->Fields('uid')."</option>";
                $ru->MoveNext();
            }
        ?>
    </select>
    
    <input type="hidden" name="delUG" />
    <input type="submit" />
</form>

---------------------------------------------------

<form method="POST">
    <p>Alteração de Dados do Grupo no LDAP</p>
    <label for="altGrupoId">Grupo</label>
    <select name="altGrupoId">
        <?php
            $l  = new LDAP();
            $rs = $l->_lst("(|(cn=*))","ou=Grupos,");
    
            while (!$rs->EOF)
            {
                echo "<option value=".$rs->Fields('cn').">".$rs->Fields('cn')."</option>";
                $rs->MoveNext();
            }
        ?>
    </select>
    <br />
    <input type="submit" />
</form>

<?php
if (isset($_POST["altGrupoId"])){
    $ldap = new LDAP();
    $rs = $ldap->_lst("(|(cn=".$_POST['altGrupoId']."))","ou=Grupos,");   
?>

<form method="POST">
    <p>Alteração de Dados do Grupo no LDAP</p>
    <label for="cn">Cn</label>
    <input type="text" name="cn" value="<?php echo $rs->Fields('cn'); ?>" />
    <br />
    <label for="permis">Permissões</label>           
    <input type="text" name="permis" value="<?php echo $rs->Fields('permis'); ?>" />
    <br />
    <label for="appId">AppId</label>
    <input type="text" name="appId" value="<?php echo $rs->Fields('appId'); ?>" />
    
    <input type="hidden" name="altGrupoDados" />
    <input type="submit" />
</form>

<?php 
}
?>

---------------------------------------------------

<form method="POST">
    <p>Deleção de Grupo no LDAP</p>
    <label for="delGrupo">Grupo</label>
    <select name="delGrupo">
        <?php
            $l  = new LDAP();
            $rs = $l->_lst("(|(cn=*))","ou=Grupos,");
    
            while (!$rs->EOF)
            {
                echo "<option value=".$rs->Fields('cn').">".$rs->Fields('cn')."</option>";
                $rs->MoveNext();
            }
        ?>
    </select>
    <br />
    <input type="submit" />
</form>
