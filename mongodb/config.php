<?php
/**
 * RockMongo configuration
 *
 * Defining default options and server configuration
 * @package rockmongo
 */
 
$MONGO = array();
$MONGO["features"]["log_query"] = "on";//log queries
$MONGO["features"]["theme"] = "default";//theme
$MONGO["features"]["plugins"] = "on";//plugins

$i = 0;

/**
* Configuration of MongoDB servers
* 
* @see more details at http://rockmongo.com/wiki/configuration?lang=en_us
*/
$MONGO["servers"][$i]["mongo_name"] = "rd01-A01";//mongo server name
//$MONGO["servers"][$i]["mongo_sock"] = "/var/run/mongo.sock";//mongo socket path (instead of host and port)
$MONGO["servers"][$i]["mongo_host"] = "211.152.8.45";//mongo host
$MONGO["servers"][$i]["mongo_port"] = "27017";//mongo port
$MONGO["servers"][$i]["mongo_timeout"] = 0;//mongo connection timeout
//$MONGO["servers"][$i]["mongo_db"] = "MONGO_DATABASE";//default mongo db to connect, works only if mongo_auth=false
$MONGO["servers"][$i]["mongo_user"] = "root";//mongo authentication user name, works only if mongo_auth=false
$MONGO["servers"][$i]["mongo_pass"] = "yisheng@2015";//mongo authentication password, works only if mongo_auth=false
$MONGO["servers"][$i]["mongo_auth"] = false;//enable mongo authentication?

$MONGO["servers"][$i]["control_auth"] = false;//enable control users, works only if mongo_auth=false
$MONGO["servers"][$i]["control_users"]["root"] = "yisheng@2015";//one of control users ["USERNAME"]=PASSWORD, works only if mongo_auth=false

#$MONGO["servers"][$i]["ui_only_dbs"] = "";//databases to display
$MONGO["servers"][$i]["ui_hide_dbs"] = "admin,local";//databases to hide
$MONGO["servers"][$i]["ui_hide_collections"] = "";//collections to hide
$MONGO["servers"][$i]["ui_hide_system_collections"] = false;//whether hide the system collections

//$MONGO["servers"][$i]["docs_nature_order"] = false;//whether show documents by nature order, default is by _id field
//$MONGO["servers"][$i]["docs_render"] = "default";//document highlight render, can be "default" or "plain"

$i ++;

/**
 * mini configuration for another mongo server
 */
$MONGO["servers"][$i]["mongo_options"] = array('replicaSet'=>'rs0');
$MONGO["servers"][$i]["mongo_name"] = "docker";
$MONGO["servers"][$i]["mongo_host"] = "mongodb://docker01,docker02,docker03";
$MONGO["servers"][$i]["mongo_port"] = false;
#$MONGO["servers"][$i]["mongo_db"] = "yisheng";
$MONGO["servers"][$i]["mongo_user"] = "root";
$MONGO["servers"][$i]["mongo_pass"] = "Ysdj23100";
$MONGO["servers"][$i]["mongo_auth"] = false;
$MONGO["servers"][$i]["control_auth"] = false;
$MONGO["servers"][$i]["ui_hide_dbs"] = "admin,local";//databases to hide
$i ++;

?>
