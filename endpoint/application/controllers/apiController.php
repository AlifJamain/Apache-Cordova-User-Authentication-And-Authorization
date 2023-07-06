<?php
namespace application\controllers;

use application\controllers\queryHelper;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
class apiController {
    public function method_post() {
        #start
        #to validate user auth if exist and store if not exist
        if( $_POST['action'] == "register" ) {
            $username = $_POST['username'];
            $password = md5( $_POST['password'] );
            
            $statement = "SELECT * FROM login_credential WHERE username = '" . $username . "' AND password = '" . $password . "'";
            $results = queryHelper::query_array( $statement );
            if( count( $results ) > 0 ) {
                $response = [
                    "status"    => 1,
                    "message"   => "Username Already Exist",
                    "result"    => [
                        [
                            "username"  => $username
                        ]
                    ],
                ];
            } else {
                $insert = [
                    
                ]
            }
        }
        #end
        
        #start
        #to validate user auth here
        if( $_POST['action'] == "login" ) {
            $username = $_POST['username'];
            $password = md5( $_POST['password'] );
            
            $response = [
                "status"    => 0,
                "message"   => "Login Successfull!",
                "result"    => [
                    [
                        "username"  => $username
                    ]
                ],
            ];
        }
        #end
        
        echo json_encode($response);
        exit();
    }
}
?>