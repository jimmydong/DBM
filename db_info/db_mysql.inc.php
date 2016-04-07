<?
/**
 * 公共文件: Mysql Access Class
 *
 * @author N/A
 * @packet JDK
 * @version 2003.12.01
 */ 

class DB_Sql {
  
  /* public: connection parameters */
  var $Host     = "";
  var $Database = "";
  var $User     = "";
  var $Password = "";

  /* public: configuration parameters */
  var $Auto_Free     = 0;     ## Set to 1 for automatic mysql_free_result()
  var $Debug         = 0;     ## Set to 1 for debugging messages.
  var $Halt_On_Error = "yes"; ## "yes" (halt with message), "no" (ignore errors quietly), "report" (ignore errror, but spit a warning)
  var $Set_log       = 0;     ## Set to 1 for add log to INSERT AND UPDATE, by jimmy.
  var $Seq_Table     = "db_sequence";

  /* public: result array and current row number */
  var $Record   = array();
  var $Row;

  /* public: current error number and error text */
  var $Errno    = 0;
  var $Error    = "";

  /* public: this is an api revision, not a CVS revision. */
  var $type     = "mysql";
  var $revision = "1.2";

  /* private: link and query handles */
  var $Link_ID  = 0;
  var $Query_ID = 0;
  var $Insert_ID = 0;
  


  function DB_Sql($query = "",$Host="",$Database="",$User="",$Password="")
  {
    if ($Host!="") $this->Host=$Host;
    if ($Database!="") $this->Database=$Database;
    if ($User!="") $this->User=$User;
    if ($Password!="") $this->Password=$Password;
    $this->query($query);
  }
  /* public: some trivial reporting */
  function link_id() {
    return $this->Link_ID;
  }

  function query_id() {
    return $this->Query_ID;
  }

  /* public: connection management */
  function connect($Database = "", $Host = "", $User = "", $Password = "") {
    /* Handle defaults */
    if ("" == $Database)
      $Database = $this->Database;
    if ("" == $Host)
      $Host     = $this->Host;
    if ("" == $User)
      $User     = $this->User;
    if ("" == $Password)
      $Password = $this->Password;
      
    /* establish connection, select database */
    if ( 0 == $this->Link_ID ) {
    
      //$this->Link_ID=mysql_pconnect($Host, $User, $Password);
      $this->Link_ID=mysql_connect($Host, $User, $Password);
      if (!$this->Link_ID) {
        $this->halt("connect($Host, $User, \$Password) failed.");
        return 0;
      }

      if (!@mysql_select_db($Database,$this->Link_ID)) {
        $this->halt("cannot use database ".$this->Database);
        return 0;
      }
    }
//by jimmy
    mysql_query("SET NAMES gb2312",$this->Link_ID);
    return $this->Link_ID;
  }

  /* public: discard the query result */
  function free() {
      @mysql_free_result($this->Query_ID);
      $this->Query_ID = 0;
  }

  /* public: perform a query */
  function query($Query_String) {
    /* No empty queries, please, since PHP4 chokes on them. */
    if ($Query_String == "")
      /* The empty query string is passed on from the constructor,
       * when calling the class without a query, e.g. in situations
       * like these: '$db = new DB_Sql_Subclass;'
       */
      return 0;

    if (!$this->connect()) {
      return 0; /* we already complained in connect() about that. */
    };
    
    //### Log model begin
    //Check and log. add by jimmy. 2003-06-19
    if ($this->Set_log)
    {
        if (eregi("^SELECT",$Query_String))
        {
            //Ok,nothing to do.
        }
        elseif (eregi("^INSERT +INTO +(.+) +\(.+\) +VALUES +\(.+\)",$Query_String,$eregi_found))
        {
            $table_name=$eregi_found[1];
            if ($table_name!="session_user" && $table_name!="System_Log" && $table_name!="Temp_Base")
            {
                $log=$GLOBALS["session_userid"]."|".date("Y-m-d  H-i-s")."|[".addslashes($Query_String)."]<br>\n";
                $temp_string=eregi_replace("(INSERT +INTO.+\(.+)(\) +VALUES +\(.+)(\))","\\1, log\\2, '{$log}'\\3",$Query_String);
                $Query_String=$temp_string;
            }
        }
        elseif (eregi("^UPDATE",$Query_String))
        {
            if (!eregi("^UPDATE +(.+) +SET +(.+) +WHERE +(.+)",$Query_String,$eregi_found))
            {
                echo ("全部更新操作——禁止！！！");
                exit;
            }
            $table_name=$eregi_found[1];
            $condition=$eregi_found[2];
            if ($table_name!="session_user" && $table_name!="System_Log" && $table_name!="Temp_Base")
            {
                $log=$GLOBALS["session_userid"]."|".date("Y-m-d H-i-s")."|[".addslashes($Query_String)."]<br>\n";
                $temp_string=eregi_replace("(UPDATE.+)(WHERE.+)","\\1, log=CONCAT(log,'${log}') \\2",$Query_String);
                $Query_String=$temp_string;
            }
        }
        elseif (eregi("^DELETE",$Query_String))
        {
            if (!eregi("^Delete +FROM +(.+) +WHERE +(.+)",$Query_String,$eregi_found))
            {
                $table_name=$eregi_found[1];
                echo ("全部删除操作——禁止 OR 语法输入错误。");
                exit;
            }
            $table_name=$eregi_found[1];
            $condition=$eregi_found[2];
            if ($table_name!="session_user" && $table_name!="System_Log" && $table_name!="Temp_Base")
            {
                $log=$GLOBALS["session_userid"]."|".date("Y-m-d  H-i-s")."|[".addslashes($Query_String)."]<br>\n";
                $temp_string="UPDATE $table_name SET del=1, log=CONCAT(log,'${log}') WHERE $condition";
                $Query_String=$temp_string;
            }
        }
        else
        {
            echo "数据库操作核心库异常／SQL基本语法错。请将下述内容反馈给开发人员，以求尽快解决。谢谢！<hr>$Query_String<hr>";
            exit;
        }
    }//end if
    //### Log model end

    # New query, discard previous result.
    if ($this->Query_ID) {
      $this->free();
    }

    if ($this->Debug)
      printf("Debug: query = %s<br>\n", $Query_String);

    $this->Query_ID = @mysql_query($Query_String,$this->Link_ID);
    $this->Insert_ID = @mysql_insert_id();
    $this->Row   = 0;
    $this->Errno = mysql_errno();
    $this->Error = mysql_error();
    if (!$this->Query_ID) {
      $this->halt("Invalid SQL: ".$Query_String);
    }

    # Will return nada if it fails. That's fine.
    return $this->Query_ID;
  }

  /* public: walk result set */
  function next_record() {
    if (!$this->Query_ID) {
      $this->halt("next_record called with no query pending.");
      return 0;
    }

    $this->Record = @mysql_fetch_array($this->Query_ID);
    $this->Row   += 1;
    $this->Errno  = mysql_errno();
    $this->Error  = mysql_error();

    $stat = $this->Record;
    if (!$stat && $this->Auto_Free) {
      $this->free();
    }
    return $stat;
  }
  function fetch_array() {
    if (!$this->Query_ID) {
      $this->halt("next_record called with no query pending.");
      return 0;
    }

    $this->Record = @mysql_fetch_array($this->Query_ID);
    $this->Row   += 1;
    $this->Errno  = mysql_errno();
    $this->Error  = mysql_error();

    $stat = is_array($this->Record);
    if (!$stat && $this->Auto_Free) {
      $this->free();
    }
    return $this->Record;
  }


  /* public: position in result set */
  function seek($pos = 0) {
    $status = @mysql_data_seek($this->Query_ID, $pos);
    if ($status)
      $this->Row = $pos;
    else {
      $this->halt("seek($pos) failed: result has ".$this->num_rows()." rows");

      /* half assed attempt to save the day, 
       * but do not consider this documented or even
       * desireable behaviour.
       */
      @mysql_data_seek($this->Query_ID, $this->num_rows());
      $this->Row = $this->num_rows;
      return 0;
    }

    return 1;
  }

  /* public: table locking */
  function lock($table, $mode="write") {
    $this->connect();
    
    $query="lock tables ";
    if (is_array($table)) {
      while (list($key,$value)=each($table)) {
        if ($key=="read" && $key!=0) {
          $query.="$value read, ";
        } else {
          $query.="$value $mode, ";
        }
      }
      $query=substr($query,0,-2);
    } else {
      $query.="$table $mode";
    }
    $res = @mysql_query($query, $this->Link_ID);
    if (!$res) {
      $this->halt("lock($table, $mode) failed.");
      return 0;
    }
    return $res;
  }
  
  function unlock() {
    $this->connect();

    $res = @mysql_query("unlock tables", $this->Link_ID);
    if (!$res) {
      $this->halt("unlock() failed.");
      return 0;
    }
    return $res;
  }


  /* public: evaluate the result (size, width) */
  function affected_rows() {
    return @mysql_affected_rows($this->Link_ID);
  }

  function num_rows() {
    return @mysql_num_rows($this->Query_ID);
  }

  function num_fields() {
    return @mysql_num_fields($this->Query_ID);
  }

  /* public: shorthand notation */
  function nf() {
    return $this->num_rows();
  }

  function np() {
    print $this->num_rows();
  }

  function f($Name) {
    return $this->Record[$Name];
  }

  function p($Name) {
    print $this->Record[$Name];
  }

  /* public: sequence numbers */
  function nextid($seq_name) {
    $this->connect();
    
    if ($this->lock($this->Seq_Table)) {
      /* get sequence number (locked) and increment */
      $q  = sprintf("select nextid from %s where seq_name = '%s'",
                $this->Seq_Table,
                $seq_name);
      $id  = @mysql_query($q, $this->Link_ID);
      $res = @mysql_fetch_array($id);
      
      /* No current value, make one */
      if (!is_array($res)) {
        $currentid = 0;
        $q = sprintf("insert into %s values('%s', %s)",
                 $this->Seq_Table,
                 $seq_name,
                 $currentid);
        $id = @mysql_query($q, $this->Link_ID);
      } else {
        $currentid = $res["nextid"];
      }
      $nextid = $currentid + 1;
      $q = sprintf("update %s set nextid = '%s' where seq_name = '%s'",
               $this->Seq_Table,
               $nextid,
               $seq_name);
      $id = @mysql_query($q, $this->Link_ID);
      $this->unlock();
    } else {
      $this->halt("cannot lock ".$this->Seq_Table." - has it been created?");
      return 0;
    }
    return $nextid;
  }

  /* public: return table metadata */
  function metadata($table='',$full=false) {
    $count = 0;
    $id    = 0;
    $res   = array();

    /*
     * Due to compatibility problems with Table we changed the behavior
     * of metadata();
     * depending on $full, metadata returns the following values:
     *
     * - full is false (default):
     * $result[]:
     *   [0]["table"]  table name
     *   [0]["name"]   field name
     *   [0]["type"]   field type
     *   [0]["len"]    field length
     *   [0]["flags"]  field flags
     *
     * - full is true
     * $result[]:
     *   ["num_fields"] number of metadata records
     *   [0]["table"]  table name
     *   [0]["name"]   field name
     *   [0]["type"]   field type
     *   [0]["len"]    field length
     *   [0]["flags"]  field flags
     *   ["meta"][field name]  index of field named "field name"
     *   The last one is used, if you have a field name, but no index.
     *   Test:  if (isset($result['meta']['myfield'])) { ...
     */

    // if no $table specified, assume that we are working with a query
    // result
    if ($table) {
      $this->connect();
      $id = @mysql_list_fields($this->Database, $table);
      if (!$id)
        $this->halt("Metadata query failed.");
    } else {
      $id = $this->Query_ID; 
      if (!$id)
        $this->halt("No query specified.");
    }
 
    $count = @mysql_num_fields($id);

    // made this IF due to performance (one if is faster than $count if's)
    if (!$full) {
      for ($i=0; $i<$count; $i++) {
        $res[$i]["table"] = @mysql_field_table ($id, $i);
        $res[$i]["name"]  = @mysql_field_name  ($id, $i);
        $res[$i]["type"]  = @mysql_field_type  ($id, $i);
        $res[$i]["len"]   = @mysql_field_len   ($id, $i);
        $res[$i]["flags"] = @mysql_field_flags ($id, $i);
      }
    } else { // full
      $res["num_fields"]= $count;
    
      for ($i=0; $i<$count; $i++) {
        $res[$i]["table"] = @mysql_field_table ($id, $i);
        $res[$i]["name"]  = @mysql_field_name  ($id, $i);
        $res[$i]["type"]  = @mysql_field_type  ($id, $i);
        $res[$i]["len"]   = @mysql_field_len   ($id, $i);
        $res[$i]["flags"] = @mysql_field_flags ($id, $i);
        $res["meta"][$res[$i]["name"]] = $i;
      }
    }
    
    // free the result only if we were called on a table
    if ($table) @mysql_free_result($id);
    return $res;
  }

  /* private: error handling */
  function halt($msg) {
    $this->Error = @mysql_error($this->Link_ID);
    $this->Errno = @mysql_errno($this->Link_ID);
    if ($this->Halt_On_Error == "no")
      return;

    $this->haltmsg($msg);

    if ($this->Halt_On_Error != "report")
      die("Session halted.");
  }

  function haltmsg($msg) {
    printf("</td></tr></table><b>Database error:</b> %s<br>\n", $msg);
    printf("<b>MySQL Error</b>: %s (%s)<br>\n",
      $this->Errno,
      $this->Error);
  }

  function table_names() {
    $this->query("SHOW TABLES");
    $i=0;
    while ($info=mysql_fetch_row($this->Query_ID))
     {
      $return[$i]["table_name"]= $info[0];
      $return[$i]["tablespace_name"]=$this->Database;
      $return[$i]["database"]=$this->Database;
      $i++;
     }
   return $return;
  }
  
//////////////////////////////////////////////////////////////////////
//
// Expand by jimmy 2001.02.14
//
//////////////////////////////////////////////////////////////////////
  function query_first($query_string) {
    // 执行查询并返回第一行
    $this->query($query_string);
    if ($this->next_record())
    {
    	mysql_free_result($this->Query_ID);
        return $this->Record;
    }
    else
    {
    	return false;
    }
  }
  
  function query_all($querystring,$maxlines=100) {
  	// 返回全部数据为表格形式
  	
  	$colorflag=0;
  	$this->query($querystring);
  	$CountRecord=$this->num_rows();
  	echo "<p>查询『".$querystring."』的全部结果：共有 $CountRecord 条纪录 <br>";
  	echo "<table border=0><tr bgcolor=#AAAAAE>";
  	$collums=mysql_num_fields($this->Query_ID);
  	for ($i=0;$i<$collums;$i++)
  	{
  		echo "<td>";
  		echo mysql_field_name($this->Query_ID,$i);
  		$thefield[$i]=mysql_field_name($this->Query_ID,$i);
  		echo "<br><font size=2 color=#444444>(";
  		echo mysql_field_type($this->Query_ID,$i);
  		echo ")</font></td>";
  	}
  	echo "</tr>";
  	$counter=0;
  	while($this->next_record() && $counter<$maxlines)
  	{
  	    $counter++;
  		if ($colorflag){echo "<tr bgcolor=#eeeeee>";$colorflag=0;}
	  		else {echo "<tr bgcolor=#cccccc>";$colorflag=1;}
	  	for ($i=0;$i<$collums;$i++)
	  	{
	  		echo "<td>";
	  		echo $this->Record[$thefield[$i]];
	  		echo "</td>";
	  	}
	  	echo "</tr>";
	}
	echo "</table>";
  }
  
  function get_fields() {
  	// 返回数据的列信息
  	$collums = mysql_num_fields($this->Query_ID);
  	for ($i=0;$i<$collums;$i++)
  	{
        $type  = mysql_field_type  ($this->Query_ID, $i);
        $name  = mysql_field_name  ($this->Query_ID, $i);
        $len   = mysql_field_len   ($this->Query_ID, $i);
        $flags = mysql_field_flags ($this->Query_ID, $i);
  	$thefield[$i] = array("type"=>$type,"name"=>$name,"len"=>$len,"flags"=>$flags);
  	}
    return $thefield;
  }

  function get_fullfields($tablename) {
       $fields = array();
       $fullmatch        = "/^([^(]+)(\([^)]+\))?(\s(.+))?$/";
       $charlistmatch    = "/,?'([^']*)'/";
       $numlistmatch    = "/,?(\d+)/";
       
       $fieldsquery .= "DESCRIBE `$tablename`";
       $result_fieldsquery = mysql_query($fieldsquery);
       $i=0;
       if($result_fieldsquery)while ($row_fieldsquery = mysql_fetch_assoc($result_fieldsquery)) {
           //$name    = $row_fieldsquery['Field'];
	   $name=$i++;
           $fields[$name] = array();
           $fields[$name]["name"]       = $row_fieldsquery['Field'];
           $fields[$name]["type"]       = "";
           $fields[$name]["args"]       = array();
           $fields[$name]["add"]        = "";
           $fields[$name]["null"]       = $row_fieldsquery['Null'];
           $fields[$name]["key"]        = $row_fieldsquery['Key'];
           $fields[$name]["default"]    = $row_fieldsquery['Default'];
           $fields[$name]["extra"]      = $row_fieldsquery['Extra'];
           
           $fulltype = $row_fieldsquery['Type'];
           $typeregs = array();
           
           if (preg_match($fullmatch, $fulltype, $typeregs)) {
               $fields[$name]["type"] = $typeregs[1];
               if ($typeregs[4]) $fields[$name]["add"] = $typeregs[4];
               $fullargs = $typeregs[2];
               $argsreg = array();
               if (preg_match_all($charlistmatch, $fullargs, $argsreg)) {
                   $fields[$name]["args"] = $argsreg[1];
               } else {
                   $argsreg = array();
                   if (preg_match_all($numlistmatch, $fullargs, $argsreg)) {
                       $fields[$name]["args"] = $argsreg[1];
                   }
               }
           }
       }else{
	var_dump($fieldsquery);
	}
//var_dump($fields);
       return $fields;
    }
  
  function fquery($Query_String) {
  $Query_String=str_replace("=","<font color=red><b> = </b></font>",$Query_String);
  $Query_String=str_replace("(","<font color=green><b> ( </b></font>",$Query_String);
  $Query_String=str_replace(")","<font color=green><b> ) </b></font>",$Query_String);
  $Query_String=str_replace(",","<font color=green><b> , </b></font>",$Query_String);
  
  $Query_String=str_replace("select ","<font color=blue><b> SELECT </b></font>",$Query_String);
  $Query_String=str_replace("SELECT ","<font color=blue><b> SELECT </b></font>",$Query_String);
  $Query_String=str_replace("insert ","<font color=blue><b> INSERT </b></font>",$Query_String);
  $Query_String=str_replace("INSERT ","<font color=blue><b> INSERT </b></font>",$Query_String);
  $Query_String=str_replace("update ","<font color=blue><b> UPDATE </b></font>",$Query_String);
  $Query_String=str_replace("UPDATE ","<font color=blue><b> UPDATE </b></font>",$Query_String);
  $Query_String=str_replace("replace ","<font color=blue><b> REPLACE </b></font>",$Query_String);
  $Query_String=str_replace("REPLACE ","<font color=blue><b> REPLACE </b></font>",$Query_String);
  $Query_String=str_replace("delete ","<font color=blue><b> DELETE </b></font>",$Query_String);
  $Query_String=str_replace("DELETE ","<font color=blue><b> DELETE </b></font>",$Query_String);

  $Query_String=str_replace(" where ","<font color=blue><b> WHERE </b></font>",$Query_String);
  $Query_String=str_replace(" WHERE ","<font color=blue><b> WHERE </b></font>",$Query_String);
  $Query_String=str_replace(" set ","<font color=blue><b> SET </b></font>",$Query_String);
  $Query_String=str_replace(" SET ","<font color=blue><b> SET </b></font>",$Query_String);
  $Query_String=str_replace(" group by ","<font color=blue><b> GROUP BY </b></font>",$Query_String);
  $Query_String=str_replace(" GROUP BY ","<font color=blue><b> GROUP BY </b></font>",$Query_String);
  $Query_String=str_replace(" order by ","<font color=blue><b> ORDER BY </b></font>",$Query_String);
  $Query_String=str_replace(" ORDER BY ","<font color=blue><b> ORDER BY </b></font>",$Query_String);
  $Query_String=str_replace(" values ","<font color=blue><b> VALUES </b></font>",$Query_String);
  $Query_String=str_replace(" VALUES ","<font color=blue><b> VALUES </b></font>",$Query_String);

  $Query_String=str_replace(" and ","<font color=red><b> AND </b></font>",$Query_String);
  $Query_String=str_replace(" AND ","<font color=red><b> AND </b></font>",$Query_String);
  $Query_String=str_replace(" or ","<font color=red><b> OR </b></font>",$Query_String);
  $Query_String=str_replace(" OR ","<font color=red><b> OR </b></font>",$Query_String);

  echo "<table border=1 width=100%><tr><td>※ $Query_String </td></tr></table><BR>";
  }

}//end class define
?>
