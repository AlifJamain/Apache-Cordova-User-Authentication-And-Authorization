<?php
namespace application\helpers;

class queryHelper {
    public static $conn = false;
    public static $conn_nocommit = false;
    public static $db_hostname = "localhost";
    public static $db_name = "albayen";
    private static $db_username = "root";
    private static $db_password = "";
    public static $db_port = "";
    public static $db_sql;
    public static $user = "";
    public static $role = "";

    public static function init( $auto_commit = true ) {
        if( $auto_commit ) {
            if( !self::$conn ) {
                self::$conn = mysqli_connect( self::$db_hostname,self::$db_username,self::$db_password,self::$db_name ); 
                if( self::$conn ) {
                    mysqli_select_db( self::$conn, self::$db_name );
                }
                
                if( mysqli_connect_errno() ) {
                    error_log( "mysqli - Failed to connect to MySQL: " . mysqli_connect_error() );
                }
            }
        } else {
            if( !self::$conn_nocommit ) {
                self::$conn_nocommit = mysqli_connect( self::$db_hostname,self::$db_username,self::$db_password,self::$db_name ); 
                if( self::$conn_nocommit ) {
                    mysqli_select_db( self::$conn_nocommit, self::$db_name );
                }
                
                if ( mysqli_connect_errno() ) {
                    error_log( "mysqli - Failed to connect to MySQL: " . mysqli_connect_error() );
                }
            }
        }
    }

    public static function escape( $str ) {
        self::init();
        $str = str_replace( "<script", "script", $str );
        $str = str_replace( "</script", "script", $str );
        return mysqli_real_escape_string( self::$conn, $str );
    }

    public static function query( $q, $autocommit = true ) {
        self::init();
        self::$db_sql = $q;
        $result = false;
        if( $autocommit ) {
            $result = mysqli_query( self::$conn, $q );
            if ( !$result ) {
                error_log( "Invalid query -- $q -- " . mysqli_error( self::$conn ) );
                echo " Invalid query -- $q -- " . mysqli_error( self::$conn ) . " ";
            }
        } else {
            self::init( false );
            mysqli_query( self::$conn_nocommit, $q );
            /*if ( !$result ) {
                error_log( "Invalid query -- $q -- " . mysqli_error( self::$conn_nocommit ) );
                echo " Invalid query -- $q -- " . mysqli_error( self::$conn_nocommit ) . " ";
            }*/
            return true;
        }
        return $result;
    }  

    public static function start_transaction() {
        self::init( false );
        mysqli_autocommit( self::$conn_nocommit, false );
    }

    public static function commit() {
        if( mysqli_commit( self::$conn_nocommit ) ) {
            //mysql_close( self::$conn_nocommit );
            self::$conn_nocommit = false;
            return true;
        }
        return false;
    }

    public static function query_delete( $tableName = "", $wherePhrase = "", $autocommit = true ) {
        return self::query( sprintf( "DELETE FROM %s %s", ( $tableName != "" ) ? $tableName : self::$db_tablename, ( $wherePhrase != "" ) ? $wherePhrase : self::$db_wherephrase ), $autocommit );
    }

    public static function query_array( $q ) {
        self::$db_sql = $q;          
        $result = self::query( $q );
        if ( !$result ) {
            error_log( "Invalid query -- $q -- " . mysqli_error( self::$conn ) );
        }

        $result_array = array();
        $count = 0;
        while( $item = mysqli_fetch_assoc( $result ) ) {
            $result_array[$count] = $item;
            $count++;
        }
        //mysql_free_result( $result ); 
        //mysql_close( self::$conn );
        return $result_array;          
    }

    public static function get_row_count( $q ) {
        $result = self::query( $q );
        $rows = mysqli_num_rows( $result );
        return $rows;
    }

    public static function query_insert( $table, $arr = array(), $autocommit = true ) {
        $sql = "";
        $sql .= "INSERT INTO ";
        $sql .= $table;
        $sql .= " ( ";
        $count = 1;
        foreach( $arr as $k => $arrItem ) {
            $sql .= "`".$k."`";
            if( $count < count( $arr ) ) $sql .= ", ";
            $count++;
        }
        $sql .= " ) ";
        $sql .= " VALUES ";
        $sql .= " ( ";
        $count = 1;
        foreach( $arr as $k => $arrItem ) {
            //$arrItem = stripslashes( $arrItem );
            //$sql .= "'" . $arrItem . "'"; //"'" . str_replace( "'", "''", $arrItem ) . "'";
            //$sql .= ( is_numeric( $arrItem ) && substr( $arrItem, 0, 1 ) != 0 ) ? $arrItem : "'" . addslashes( $arrItem ) . "'";
            $isExcep = preg_match( '/^<<.*>>$/', $arrItem ); //check if contains <>, then trim <<>>
            if( ctype_digit( $arrItem ) && substr( $arrItem, 0, 1 ) != 0 ) {
                $res = $arrItem;
            } else {
                $isExcep == true;
            }

            if( $isExcep == true ) {
                $res = ltrim( rtrim( $arrItem, '>>' ), '<<' );
            } else {
                $res = "'" . addslashes( $arrItem ) . "'";
            }
            $sql .= $res;
            if( $count < count( $arr ) ) {
                $sql .= ", ";
            }
            $count++;
        }
        $sql .= " ) ";
        return self::query( $sql, $autocommit );
    }

    public static function query_update( $table, $where, $arr = array(), $autocommit = true ) {
        $sql = "";
        $sql .= "UPDATE ";
        $sql .= $table;
        $sql .= " SET ";
        $count = 1;
        foreach( $arr as $k => $arrItem ) {
            $sql .= "`" . $k . "`=";
            //$sql .= "'" . $arrItem . "'"; //$sql .= ( $arrItem != '' ) ? "'" . str_replace( "'", "''", $arrItem ) . "'" : "''";
            //$sql .= ( is_numeric( $arrItem ) && substr( $arrItem, 0, 1 ) != 0 ) ? $arrItem : "'" . addslashes( $arrItem ) . "'";
            $isExcep = preg_match( '/^<<.*>>$/', $arrItem ); //check if contains <>, then trim <<>>
            if( ctype_digit( $arrItem ) && substr( $arrItem, 0, 1 ) != 0 ) {
                $res = $arrItem;
            } else {
                $isExcep == true;
            }
            
            if( $isExcep == true ) {
                $res = ltrim( rtrim( $arrItem, '>>' ), '<<' );
            } else {
                $res = "'" . addslashes( $arrItem ) . "'";
            }
            $sql .= $res;
            if ( $count < count( $arr ) ) {
                $sql .= ", ";
            }
            $count++;
        }
        $sql .= " " . $where;
        return self::query( $sql, $autocommit );
    }
}
?>