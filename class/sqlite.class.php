<?php

function sqlite_escape_string( $string ){
    return SQLite3::escapeString($string);
}

class SQLite
{
	// SQLite database use and managment.
	public static function get_db_flows() {
		$dbname=dirname(__FILE__).'/../data/bases/base_flows.sqlite';
        //echo $dbname;
		if(!class_exists('SQLite3')) {
		  die("SQLite 3 NOT supported.");
		}

        /*if(!file_exists($dbname)) {
    	  die("database not exists.");
    	}*/

        return new SQLite3($dbname);
		 
	}

	// SQLite database use and managment.
	public static function get_db_items($flow) {
		$dbname=dirname(__FILE__).'/../data/bases/base_items_'.md5($flow->getUrl()).".sqlite";

		if(!class_exists('SQLite3')) {
		  die("SQLite 3 NOT supported.");
		}

        /*
    	if(!file_exists($dbname)) {
    	  die("database not exists.");
    	}*/
		 
		return new SQLite3($dbname);
		 
	}

	public static function create_flows_table() {
		$mytable ="flows";
		$base = SQLite::get_db_flows();
		 
		$query = "CREATE TABLE IF NOT EXISTS $mytable(
					ID_FLOWS bigint(20) NOT NULL PRIMARY KEY,
					name VARCHAR(255),
					url VARCHAR(255),
					update_date NUMERIC NOT NULL,
					number_of_articles INTEGER NOT NULL,
					comment VARCHAR(255))";
					 
		$results = $base->exec($query);
	}

	public static function create_items_for_flows_table($flow) {
		$mytable ="items";
		$base = SQLite::get_db_items($flow);
		 
		$query = "CREATE TABLE IF NOT EXISTS $mytable(
					ID_ITEMS bigint(20) NOT NULL PRIMARY KEY,
					title VARCHAR(255),
					url VARCHAR(255),
					date NUMERIC NOT NULL,
					description TEXT,
					guid TEXT)";

		$results = $base->exec($query);
	}

	public static function get_items_datas_from_flow_and_url($flow,$url) {
		$mytable ="items";
		$base = SQLite::get_db_items($flow);
		 
		$query = "SELECT * FROM $mytable
						WHERE url='$url'";
		$row = $base->querySingle($query,true);

        if(isset($row) && !empty($row))
		{
			return new Items($row['ID_ITEMS'], $row['title'], $row['url'], $row['date'], $row['description'], $row['guid']);
		}
		
		return null;
	}

	public static function get_flow_datas_from_url($url) {
		$mytable ="flows";
		$base = SQLite::get_db_flows();
		 
		$query = "SELECT * FROM $mytable
						WHERE url='$url'";
		$results = $base->query($query);
		$row = $results->fetchArray();

		if(isset($row) && !empty($row))
		{
			return new Flows($row['ID_FLOWS'], $row['name'], $row['url'], $row['update_date'], $row['number_of_articles'], $row['comment']);
		}
		
		return null;
	}

	public static function get_max_items_id_in_database_from_flow($flow) {
		$mytable ="items";
		$base = SQLite::get_db_items($flow);
		 
		$query = "SELECT max(ID_ITEMS) as ID_ITEMS FROM $mytable";
		$results = $base->query($query);
		$row = $results->fetchArray();
		 
		if(isset($row) && !empty($row))
		{
			return $row['ID_ITEMS'];
		}
		
		return null;
	}


    public static function get_max_flows_id_in_database() {
		$mytable ="flows";
		$base = SQLite::get_db_flows();
		 
		$query = "SELECT max(ID_FLOWS) as ID_FLOWS FROM $mytable";
		$results = $base->query($query);
		$row = $results->fetchArray();
		 
		if(isset($row) && !empty($row)) {
			return $row['ID_FLOWS'];
		}
		
		return null;
	}

    public static function get_flows_in_database($min=null,$limit=null) {
        $return = null;
		$mytable ="flows";
		$base = SQLite::get_db_flows();
		 
		$query = "SELECT ID_FLOWS as id, name, url, update_date, number_of_articles, comment FROM $mytable";
        
        if($min != null || $limit != null) {
            $query .= " WHERE";
            if($min != null) {
                $query .= " ID_FLOWS >".$min;
            }
            if($limit != null) {
                if($min != null) {
                    $query .= " AND";
                    $limit += $min;
                }

                $query .= " ID_FLOWS <".$limit;
            }
        }

        $results = $base->query($query);

        while($row = $results->fetchArray(SQLITE3_ASSOC)) {
            if($return==null) {
                $return = array();
            }
            
            if($row != null && count($row)>0) {
                array_push ($return, new Flows($row['id'], $row['name'], $row['url'], $row['update_date'], $row['number_of_articles'], $row['comment']));
            }
		}
		
		return $return;
	}

    public static function delete_duplicate_flows() {
    	$mytable ="flows";
		$base = SQLite::get_db_flows();

		if(isset($base) && !empty($base)) {
			return $base->exec("delete from flows where rowid not in (select min(rowid) from flows group by url)");
		}

		return null;
    }

    public static function flows_exist_in_database($flows) {
        $mytable ="flows";
		$base = SQLite::get_db_flows();

		if(isset($base) && !empty($base)) {
			return $base->querySingle("select 1 from flows where url='".sqlite_escape_string ($flows->getUrl())."'") == 1;
		}

		return null;
    }

    public static function insert_flow_datas_in_database($flows) {
		$mytable ="flows";
			$base = SQLite::get_db_flows();

		if(isset($base) && !empty($base) && !SQLite::flows_exist_in_database($flows)) {
			$newid = SQLite::get_max_flows_id_in_database();
		
			if(!isset($newid) || empty($newid)) {
				$newid = 0;
			}

			$date = $flows->getUpdateDate();
			
			if(empty($date)) {
				$date = 0;
			}

			$query = "INSERT INTO $mytable (ID_FLOWS, name, url, update_date, number_of_articles, comment)
							VALUES (".($newid+1).",'".
							sqlite_escape_string ($flows->getName())."','".
							sqlite_escape_string ($flows->getUrl())."',".
							$date.",".
							$flows->getNumberOfArticles().",'".
							sqlite_escape_string ($flows->getComment())."')";

			return $base->exec($query);
		}
		return null;
	}

	public static function delete_flow_datas_in_database($flows) {
		$mytable ="flows";
			$base = SQLite::get_db_flows();

		if(isset($base) && !empty($base)) {
			$query = "DELETE from $mytable WHERE url='".$flows->getUrl()."'";

			return $base->exec($query);
		}
		return null;
	}

	public static function insert_item_datas_in_items_database($flow,$item) {
		$mytable ="items";
		$base = SQLite::get_db_items($flow);

		if(isset($base) && !empty($base)) {
			$newid = SQLite::get_max_items_id_in_database_from_flow($flow);

			if(!isset($newid) || empty($newid)) {
				$newid = 0;
			}

			$query = "INSERT INTO $mytable (ID_ITEMS, title, url, date, description, guid)
							VALUES (".($newid+1).",'".
							sqlite_escape_string ($item->getTitle())."','".
							sqlite_escape_string ($item->getUrl())."',".
							$item->getDate().",'".
							sqlite_escape_string ($item->getDescription())."','".
							sqlite_escape_string ($item->getGuid())."')";

			return $base->exec($query);
		}
		return null;
	}

	public static function delete_item_datas_in_items_database($flow,$item) {
		$mytable ="items";
		$base = SQLite::get_db_items($flow);

		if(isset($base) && !empty($base)) {
			$newid = SQLite::get_max_items_id_in_database_from_flow($flow);

			if(!isset($newid) || empty($newid)) {
				$newid = 0;
			}

			$query = "DELETE FROM $mytable WHERE url = '".$item->getUrl()."'";

			return $base->exec($query);
		}
		return null;
	}

}
