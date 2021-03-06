<?php
namespace modules\hoduser\service;
    use framework\lib\service\BaseService;

    class User extends  BaseService{

        function logout($userId){
            $user=$this->getUserById($userId);
            if($user) {
                $user->hash=false;
                $this->db->saveModel($user, "user");
            }
        }
        function getAccountForLogin($username,$password){
            $result= $this->db->select("user")
                ->where("username='".$username."' && password='".md5($password)."' && (activation='0' || activation is null)")->fetchModel("user");

            if($result){
                $result->newHash();
                $this->db->saveModel($result,"user");
            }
            return $result;
        }

        function createSessionForUser($user){
            $user->newHash();
            $this->db->saveModel($user,"user");
        }

        function register($registrationForm){
            $user=$this->model->user->fromRegistrationForm($registrationForm);
            $user->makeInactive();
            $this->db->saveModel($user,"user");
            return $user;
        }

        function getDefaultGroup(){
            $result= $this->db->select("user_group")
                ->where("is_default='1'")->fetchModel("userGroup");
            return $result;
        }

        function activateByCode($code){
            $user=$this->getByActivationCode($code);
            if($user){
                $user->activate();
                $this->db->saveModel($user,"user");
                return true;
            }
            return false;
        }

        function save($user){
            $this->db->saveModel($user,"user");
        }

        function getByActivationCode($code){
            return $this->db->select("user")
                ->where("activation='".$code."'")->fetchModel("user");
        }

        function getByResetCode($code){
            return $this->db->select("user")
                ->where("resetCode='".$code."'")->fetchModel("user");
        }

        function getByEmail($email){
            return $this->db->select("user")
                ->where("email='".$email."'")
                ->fetchModel("user");
        }

        function getUserById($id){
            return $this->db->select("user")
                ->where("id='".$id."'")->fetchModel("user");
        }

        function getUserByName($username){
            return $this->db->select("user")
                ->where("username='".$username."'")->fetchModel("user");
        }

        function getAll(){
            return $this->db->select("user")
                ->fetchAllModel("user");
        }

        function getUserByHash($hash){
            if(!empty($hash)) {
                $query = $this->db->query("select * from user where hash='" . $hash . "'");
                if ($query && $query->result) {
                    $result = $query->fetchModel("user");
                    return $result;
                }
            }
            return 0;
        }

        function getLevel($id,$type,$key){
            $query=false;

            if($id){
                $query=$this->db->query("select max(uga.level) as level from user as u
                    left join user_group as ug on ug.id=u.user_group_id
                    left join user_group_access as uga on uga.user_group_id=ug.id
                    where
                    u.id='".$id."'
                    and (uga.`type`='".$type."' or uga.`type`='*')
                    and (uga.`key`='".$key."' or uga.`key`='*');

                ");
            }else{
                $query=$this->db->query("select max(uga.level) as level from  user_group as ug
                    left join user_group_access as uga on uga.user_group_id=ug.id
                    where
                    ug.is_guest='1'
                    and (uga.`type`='".$type."' or uga.`type`='*')
                    and (uga.`key`='".$key."' or uga.`type`='*')");
            }

            if($query && $query->numRows()){
                $fetch = $query->fetch();
                if(!$fetch["level"]){
                    $fetch["level"]=0;
                }
                return $fetch["level"];
            }

            return 0;
        }
    }
?>
